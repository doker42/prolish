<?php

namespace App\Http\Controllers;

use App\Mail\JoinCompanyResponse;
use App\Mail\VerifyNewEmailMail;
use App\Mail\WelcomeEmail;
use App\Models\ActivityLog;
use App\Models\AdditionalUserCompanies;
use App\Models\Contact;
use App\Models\Invitation;
use App\Models\Membership;
use App\Models\ProjectVisibility;
use App\Models\Company;
use App\Models\Project;
use App\Models\User;
use App\Models\UserFavouriteProject;
use App\Models\UserSettings;
use App\Models\VerifyNewEmail;
use App\Models\VerifyUser;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Stripe\Customer;

class UserController extends Controller
{

    public function index(Request $request)
    {
        $user = User::where('name', 'like', '%' . $request->get('query') . '%');
        $order_field = $request->get('order_field', 'created_at');
        $order_dir = $request->get('order_dir', 'ASC');

        if ($request->get('belongs')) {
            $user = $user->where('company_id', $request->get('belongs'));
        }

        if (Auth::user()->role == 'super_user') {
            $users_collection = $user->orderBy($order_field, $order_dir)->paginate();
            foreach ($users_collection as $user_item) {
                $user_item->can_update = true;
            }
            return $users_collection;
        }

        $auth_user = Auth::user();
        $main_company = $auth_user->company_id;
        $additional_companies = AdditionalUserCompanies::where('user_id', $auth_user->id)->pluck('role', 'company_id');
        $companies = Company::where('parent_company', $main_company)
            ->orWhere('id', $main_company)
            ->orWhereIn('id', $additional_companies)
            ->orWhere('owner_id', $auth_user->id)
            ->pluck('id');

        $additional_users = AdditionalUserCompanies::whereIn('company_id', $companies)->get();
        $users_roles = [];
        foreach($additional_users as $item){
            $users_roles[$item->user_id] = $item->role;
        }
        $users_collection = $user->where(function ($query) use ($companies, $users_roles){
            $query->whereIn('company_id', $companies)->orWhereIn('id', array_keys($users_roles));
        })->orderBy($order_field, $order_dir)->paginate();


        $cur_user_permisions = USer::manager()->getUserRoles($auth_user->id)['company'];

        foreach($users_collection as $user_item){
            if($auth_user->role == 'super_user' || ($user_item != 'super_user' && Company::withTrashed()->where('owner_id', $user_item->id)->pluck('id')->count() == 0 &&
                isset($cur_user_permisions[$user_item->company_id]) && $cur_user_permisions[$user_item->company_id] == 'administrator')){
                $user_item->can_update = true;
            } else {
                $user_item->can_update = false;
            }
            $user_item->role = isset($users_roles[$user_item->id])?$users_roles[$user_item->id]:$user_item->role;
        }

        return $users_collection;
    }

    public function switchCompany(int $company_id)
    {
       if (Company::where('owner_id', Auth::user()->id)->where('id', $company_id)->count() > 0){
           $user = User::where('id', Auth::user()->id)->first();
           $user->company_id = $company_id;
           $user->save();
       }
    }

    /**
     * Searches company invites by email.
     *
     * @return Response
     */
    public function searchInvite(Request $request):JsonResponse
    {
        $data = [];
        if(!empty($invitation = Invitation::where('email', $request->get('email'))->where('company_id','>',0)->first())){
            $data =  ['id' => $invitation->company_id, 'title' => $invitation->company->title];
        }

        return response()->json([
            'result' => true,
            'data' => json_encode($data),
        ]);

    }

    public function me()
    {

        $user = Auth::user();
        dd($user);
        $verify_new_email_request = VerifyNewEmail::where('user_id', $user->id)->first();
        if (!empty($verify_new_email_request)){
            $user->isset_change_email_request = true;
        }
        $user_company = Company::withTrashed()->where('id', $user->company_id)->first();
        $user->company = $user_company;
        $user->formatted_until = is_null($user_company->active_until)?'':Carbon::parse($user_company->active_until)->format('Y-m-d');
        if($user->id == $user_company->owner_id){
            if(empty($user_company->stripe_id)){
                $stripe_customer = Customer::create([
                    'email' => $user->email,
                    'description' => $user_company->description,
                    'name' => $user_company->title,
                ], Company::getStripeKey());
                if($stripe_customer){
                    $user_company->stripe_id = $stripe_customer->id;
                    $user_company->save();
                    $user->company = $user_company;
                }
            }
        }
        return $user;
    }

    /* Returns users perosnal permissions
    *
     *
     */
    public function personalPermissions()
    {
        $user = Auth::user();
        return User::manager()->getUserRoles($user->id);
    }

    public function show($id)
    {
        return User::find($id);
    }

    public function updatePassword(Request $request){

        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'current_password' => 'nullable|required_with:new_password',
            'new_password' => 'nullable|required_with:current_password|min:5|different:current_password',
            'password' => 'min:5'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors'=>$validator->errors()], 401);
        }

        $user = User::find($id ?? Auth::user()->id);

        if (!(Auth::user()->role != 'super_user' && Auth::user()->role != 'administrator') || $user->email == Auth::user()->email) {
            if (!Hash::check($request->get('current_password'), $user->password)) {
                return response()->json(['errors' => ['message' => 'Wrong password']], 401);
            }
        }

        $value = $request->get('new_password') ?? $request->get('password');
        $password = Hash::make($value);
        $user->password = $password;
        $user->save();
        $data = ['message' => 'success',
            'notification' => trans('custom.password_updated_successfully'),
        ];

        return $data;
    }

    public function update(Request $request, $id = null)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors'=>$validator->errors()], 401);
        }

        if (!filter_var($request->get('email'), FILTER_VALIDATE_EMAIL)) {
            return response()->json(['errors'=>trans('validation.email', ['attribute' => 'email'])], 401);
        }

        $user = User::find($id ?? Auth::user()->id);

        $user->name = $request->get('name');
        $user->company_id = $request->get('company_id');
        $user->picture = $request->get('picture');
        $user->phone = $request->get('phone');
        $user->job_title = $request->get('job_title');
        $result_notification = trans('custom.data_saved_successfully');
        if ($user->email != $request->get('email')){
            $isset_user = User::where('email',$request->get('email'))->first();
            if (!empty($isset_user)){
                return response()->json(['errors'=>trans('custom.email', ['attribute' => 'email'])], 401);
            }
            VerifyNewEmail::where('user_id', $user->id)->delete();
            $verify_new_mail = VerifyNewEmail::create([
                'user_id' => $user->id,
                'email' => $request->get('email'),
                'token' => str_random(40),
            ]);
            Mail::to($request->get('email'))->send(new VerifyNewEmailMail($verify_new_mail));
            $result_notification = trans('custom.new_email_validation.confirm_receiving_request');
        }

        $roles = config('roles');

        $current_user = Auth::user();
        if ($current_user->role == 'administrator') {
            unset($roles['super_user']);
        }

        $user->role = (!empty($roles[$request->get('role')]) ? $request->get('role') : 'visitor');

        $user->save();

        Contact::where(['email' => $user->email, 'phone' => ''])->update(['phone' => $user->phone]);

        $data = ['message' => 'success',
                'notification' => $result_notification
        ];

        return $data;
    }

    /*
     * Deletes current user VerifyNewEmail tokens
     */
    public function deleteChangeEmailRequest()
    {
        $user = Auth::user();
        $verify_new_email_request = VerifyNewEmail::where('user_id', $user->id)->first();

        if (!empty($verify_new_email_request)){
            VerifyNewEmail::where('user_id', $user->id)->delete();
           $message = trans('custom.new_email_validation.request_deleted');
        } else {
            $message = trans('custom.new_email_validation.request_didnt_find');
        }
        $data = ['message' => 'success',
            'notification' => $message
        ];

        return $data;
    }

    /*
     *
     */
    public function verifyNewEmail($token)
    {
        $verifyNewEmail = VerifyNewEmail::where('token', $token)->first();
        if(isset($verifyNewEmail)){
            $user = $verifyNewEmail->user;
            $user->email = $verifyNewEmail->email;
            $user->save();
            VerifyNewEmail::where('user_id', $user->id)->delete();
            $settings = $user->settings;
            $locale = 'en';
            if (!empty($settings)){
                $data = $settings->data;
                if (isset($data['locale'])){
                    $locale = $data['locale'];
                }
            }
            $status = trans('custom.new_email_validation.verified', [], $locale);
        } else {
            $status = trans('custom.new_email_validation.the_link_is_not_available');
        }

        if (Auth::check()){
            return redirect('/#/settings')->with('vue-warning', $status);
        } else{
            return redirect('/login')->with('warning', $status);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required',
            'email' => 'required|unique:users,email',
            'password' => 'required|min:5',
            'role' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors'=>$validator->errors()], 401);
        }

        if (!filter_var($request->get('email'), FILTER_VALIDATE_EMAIL)) {
            return response()->json(['errors'=>trans('validation.email', ['attribute' => 'email'])], 401);
        }

        $password = Hash::make($request->get('password'));
        $roles = config('roles');

        $current_user = Auth::user();
        if ($current_user->role == 'administrator') {
            unset($roles['super_user']);
        }

        $company_id = $request->get('company_id');

        $vew_company_created = false;

        if (!$company_id) {
            $membership = Membership::where('id', Membership::FREE_PACKAGE_ID)->first();

            $company = Company::create([
                'title' => $request->get('name'),
                'status' => 1,
                'storage_used' => 0,
                'logo' => '/images/user_pick_default.png',
                'parent_company' => null,
                'membership_id' => $membership->id,
            ]);
            $vew_company_created = true;
            $company_id = $company->id;
        }

        $user = User::create([
            'name' => $request->get('name'),
            'email' => $request->get('email'),
            'password' => $password,
            'role' => (!empty($roles[$request->get('role')]) ? $request->get('role') : 'visitor'),
            'company_id' => $company_id,
            'verified' => 1,
            'phone' => $request->get('phone')
        ]);

        if($vew_company_created) {
            $company = Company::where($company_id)->first();
            if($company) {
                $company->owner_id = $user->id;
                $company->save();
            }
        }

        Contact::where(['email' => $user->email, 'phone' => ''])->update(['phone' => $user->phone]);

        return ['message' => 'success'];
    }

    public function destroy($id)
    {
        if ($id == Auth::user()->id) {
            return response()->json(['errors'=> ['message' => 'You can\'t delete yourself']], 408);
        }

        $user = User::find($id);

        $user->email = $user->email . 'DELETED';
        $user->save();

        $user->delete();

        return ['message' => 'success'];
    }

    public function roles(Request $request)
    {
        $roles = config('roles');

        switch ($request->get('limit')) {
            case 'share':
                unset($roles['super_user'], $roles['administrator']);
                break;

            case 'invite':
                unset($roles['super_user']);
                break;

            case 'edit':
                if (Auth::user()->role == 'administrator') {
                    unset($roles['super_user']);
                }
                break;
            default:
                return $roles;
                break;
        }

        return $roles;
    }

    /*
     * returns current User's view settings
     */
    public function userSettings(Request $request)
    {
        $user = Auth::user();
        $settings = $user->settings;

        if (empty($settings)) {
            return [];
        }
        
        if (!$request->get('settings_key')) {
            return $settings->data;
        }

        $data_to_return = isset($settings->data[$request->get('settings_key')]) ? $settings->data[$request->get('settings_key')] : [];

        if (ActivityLog::where('user_id',Auth::user()->id)->count() == 0 &&
            Auth::user()->id == Auth::user()->company->owner_id
            && Auth::user()->company->verified == Company::COMPANY_NOT_VERIFIED){

            $data_to_return['show_welcome_intro'] = 1;
        }

        return $data_to_return;

    }


    /*
     * processes company administrator's decision
     * to approve or decline a new user's request
     */
    public function companyApproveUser($token, $action, $lang)
    {
        $verifyUser = VerifyUser::where('token', $token)->first();
        $user = $verifyUser->user;
        if (isset($verifyUser) && !empty($user) && in_array($action, ['approve', 'decline'])) {
            $user_email = $user->email;
            Mail::to($user_email)->send(new JoinCompanyResponse($action, $user->belongToCompany->title, $lang));
            if ($action == 'approve') {
                $user->verified = User::VERIFIED_VERIFIED;
                $user->save();
                $response = trans('custom.company_join_validation.request_been_confirmed', ['company_name' => $user->belongToCompany->title, 'user_name' => $user->name]);
                Mail::to($user_email)->send(new WelcomeEmail($user));
            } else {
                $response = trans('custom.company_join_validation.request_been_declined', ['company_name' => $user->belongToCompany->title, 'user_name' => $user->name], $lang);
                $user->delete();
            }



            return view('message',
                [
                    'message' => $response,
                ]);
        } else {
            return redirect('/login')->with('warning', trans('custom.email_validation.cannot_verified', [], $lang));
        }
    }


    /*
     * updates user's settings by key
     */
    public function updateUserSettings(Request $request)
    {
        if (!$request->get('settings_key') || !$request->get('settings_value')) {
            throw new \Exception('Setting key is missing');
        }

        $user = Auth::user();
        $settings = $user->settings;
        if (empty($settings)){
            $settings = UserSettings::create([
                'user_id' => $user->id,
            ]);
        }
        $data = $settings->data;
        $data[$request->get('settings_key')] = $request->get('settings_value');
        $settings->data = $data;
        $settings->save();

        return ['message' => 'success'];
    }

    public function companies(Request $request)
    {

        if ($request->get('for_visibility')) {
            return Company::orderBy('title')->with('admins')->get();
        }

        $user = Auth::user();
        $main_company = $user->company_id;
        $companies = collect();
        $favourites = UserFavouriteProject::where('user_id', $user->id)->pluck('project_id');
        $visible = ProjectVisibility::where('company_id', $user->company_id)->whereNull('user_id')->orWhere('user_id', $user->id)->get();
        $visible_array = $visible->pluck('project_id')->toArray();
        $not_active_projects = DB::table('projects')->select('id', 'deleted_at')
            ->whereIn('company_id', DB::table('companies')->whereNotNull('deleted_at')->pluck('id')->toArray())
            ->orWhereIn('company_id', DB::table('companies')->where('verified', 0)->pluck('id')->toArray())
            ->orWhereIn('company_id', DB::table('companies')->whereNotNull('active_until')->where('active_until', '<', Carbon::now())->pluck('id')->toArray())
            ->pluck('id');
        $visible_array = array_unique($visible_array);
        $visible_array = array_diff($visible_array, $not_active_projects->toArray());
        $all_count = 0;
        $trashed_count = 0;
        $favorites_count = 0;
        $shared_count = 0;


        if ($user->role == 'super_user') {
            $companies = Company::withCount('projects')->orderBy('title')->with('admins')->get();

            foreach ($companies as $company) {
                $all_count += $company->projects_count;
                $company->list_title = $company->title . ' (' . $company->projects_count . ')';
            }
            $favourites_projects = Project::whereIn('id', $favourites)->get();
            $visible_projects = Project::whereIn('id', $visible_array)->get();
            $projects = Project::onlyTrashed()->get();
            $trashed_count = $projects->count();
            $favorites_count = $favourites_projects->count();
            $shared_count = $visible_projects->count();


        } else {
            $additional_companies = AdditionalUserCompanies::where('user_id', $user->id)->get();

            $company_ids = Company::where(function ($query) use ($user) {
                // Limit viewability
                if ($user->role == 'administrator') {
                    $query->where('parent_company', $user->company_id);
                }
            })
                ->orWhere('id', $user->company_id)
                ->orWhere('owner_id', $user->id)
                ->orWhereIn('id', $additional_companies->pluck('company_id'))
                ->pluck('id');

            $projects = Project::whereIn('company_id', $company_ids)
                ->whereNotIn('id', $not_active_projects)
                ->orWhereIn('id', $visible_array)
                ->get();
            $all_count = $projects->count();
            $trashed_projects = Project::onlyTrashed()
                ->whereNotIn('id', $not_active_projects)
                ->where(function ($query) use ($request, $company_ids, $visible_array) {
                    $query->whereIn('company_id', $company_ids);
                    $query->orWhereIn('id', $visible_array);
                });
            $favourites_projects = Project::whereIn('id', $favourites)
                ->where(function ($query) use ($request, $company_ids, $visible_array) {
                    $query->whereIn('company_id', $company_ids);
                    $query->orWhereIn('id', $visible_array);
                })->get();
            $visible_projects = Project::whereIn('id', $visible_array)->get();
            $trashed_count = $trashed_projects->count();
            $favorites_count = $favourites_projects->count();
            $shared_count = $visible_projects->count();
            $companies = Company::whereIn('id', $company_ids)->orderBy('title')->get();


            $companies_project_numbers = [];
            foreach ($projects as $project) {
                if (!isset($companies_project_numbers[$project->company_id])) {
                    $companies_project_numbers[$project->company_id] = 1;
                } else {
                    $companies_project_numbers[$project->company_id] += 1;
                }
            }
            foreach ($companies as $company) {
                $company->projects_count = isset($companies_project_numbers[$company->id]) ? $companies_project_numbers[$company->id] : 0;
                $company->list_title = $company->title . ' (' . $company->projects_count . ')';
            }

        }


        $companies->prepend([
            'divider' => true
        ]);

        $companies->prepend([
            'id' => 'archived',
            'title' => trans('custom.recycle_bin'),
            'list_title' => trans('custom.recycle_bin') . ' (' . $trashed_count . ')',
            'projects_count' => $trashed_count,
        ]);

        $companies->prepend([
            'id' => 'favourite',
            'title' => trans('custom.favourite'),
            'list_title' => trans('custom.favourite') . ' (' . $favorites_count . ')',
            'projects_count' => $favorites_count,
        ]);
        if ($user->role != 'super_user') {
            $companies->prepend([
                'id' => 'shared',
                'title' => trans('custom.shared_with_me'),
                'list_title' => trans('custom.shared_with_me') . ' (' . $shared_count . ')',
                'projects_count' => $shared_count,
            ]);
        }

        $companies->prepend([
            'id' => 'all',
            'title' => trans('custom.all_projects'),
            'list_title' => trans('custom.all_projects') . ' (' . $all_count . ')',
            'projects_count' => $all_count,
        ]);


        return $companies;
    }
}
