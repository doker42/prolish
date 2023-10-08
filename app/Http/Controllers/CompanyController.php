<?php

namespace App\Http\Controllers;

use App\Domain\Company\CompanyManager;
use App\Jobs\CompanyStorageCreateJob;
use App\Jobs\CopyProject;
use App\Mail\AdminNotification;
use App\Mail\TrialNotification;
use App\Mail\CompanyVerification;
use App\Models\AdditionalUserCompanies;
use App\Models\Company;
use App\Models\Industry;
use App\Models\Invitation;
use App\Models\ProjectGalleryImage;
use App\Models\ProjectVisibility;
use App\Models\Specialization;
use App\Models\User;
use App\Models\UserSettings;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Mail;
use App\Mail\InviteToCompanyNotify;
use App\Models\Project;
use App\Mail\UserLeftEntity;
use App\Mail\EntityAccessDenied;
use App\Helpers\NextCloudHelper;
use Illuminate\Support\Facades\Session;


class CompanyController extends Controller
{
    /**
     * Show the application dashboard.
     *
     * @return array
     */
    public function index()
    {
        if (Auth::user()->role == 'super_user') {
            return Company::with('parent', 'membership')->with('specializations')->orderBy('title')->get();
        } else {
            $main_company = Auth::user()->company_id;
            $additional_companies = AdditionalUserCompanies::where('user_id', Auth::user()->id)->pluck('company_id');
            $companies = Company::where('parent_company', $main_company)->orWhere('id', $main_company)->orWhere('owner_id', Auth::user()->id)->pluck('id');

            return Company::whereIn('id', $companies)->orWhereIn('id', $additional_companies)->with('parent', 'membership')->orderBy('title')->get();
        }
    }

    /**
    * Returns filtered files from a temp storage by a current company by a selected order
    */
    public function indexTempFiles(Request $request)
    {
        //$nextcloud = new NextCloudHelper();
        //$company = Auth::user()->company;
        //$NextCloudHelper = new NextCloudHelper();
        //Company::manager()->initiateNextCloud($company, true);
        //$free_mb_space = $company->membership->size - $company->storage_used;
        //$NextCloudHelper->updateTempFolderLimit($company, (int) $free_mb_space);
        //$files_list = Company::manager()->getTemporaryFiles(Auth::user()->company_id);
        //Session::flush();
        //$webdav = Company::manager()->getWebDavAdapter(Auth::user()->company);
       	//$rest =  $NextCloudHelper->createTempFolder($company);
	//$test = $NextCloudHelper->createTempFolder($company);
	//dd($webdav);
        //exit();


        $order_field = $request->get('order_field', 'created_at');
        $order_dir = $request->get('order_dir', 'DESC');
        $per_page = $request->get('per_page', false);
        $page = $request->get('page', false);

        //dd(Session::all());

        $webdav = Company::manager()->getWebDavAdapter(Auth::user()->company);

        if (!empty($webdav)) {

            try {

                $files_list = Company::manager()->getTemporaryFiles(Auth::user()->company_id);

            } catch (\Exception $e){

                return response()->json(['errors'=>$e->getMessage()], 403);
            }

            usort($files_list,
                function ($a, $b) use ($order_field, $order_dir) {
                    if ($a[$order_field] == $b[$order_field]) {
                        return 0;
                    }
                    return $order_dir == 'ASC' ? ($a[$order_field] > $b[$order_field] ? 1 : -1) : ($a[$order_field] < $b[$order_field] ? 1 : -1);
                });

            $page = $page ?: (Paginator::resolveCurrentPage() ?: 1);
            $items = Collection::make($files_list);
            return new LengthAwarePaginator($items->forPage($page, $per_page), $items->count(), $per_page, $page, []);
        } else {
            return response()->json(['errors'=>trans('custom.no_webdav_folder')], 403);
        }
    }

    /**
     * Shows full list of verified companies.
     *
     * @return array
     */
    public function indexAllVerified()
    {
        return Company::whereNull('deleted_at')->where('verified', Company::COMPANY_VERIFIED)->where(function($query){
            $query->whereNull('active_until');
            $query->orWhere('active_until', '>', \Illuminate\Support\Carbon::now());
        })->get();
    }


    /**
    * Returns current company's temporary storage credentials
    */
    public function getTempStorageCredentials() {
        return response()->json([
            'result' => true,
            'webdav_login' => Auth::user()->company->temporary_folder,
            'webdav_url' => Auth::user()->company->webdav_url,
            'webdav_pass' => Auth::user()->company->storage_pass,
        ]);
    }

    /**
     * Shows allowed list of verified companies.
     *
     * @return array
     */
    public function indexVerified()
    {
        if (Auth::user()->role == 'super_user') {
            return Company::with('parent', 'membership')->with('specializations')->orderBy('title')->get();
        } else {
            $main_company = Auth::user()->company_id;
            $additional_companies = AdditionalUserCompanies::where('user_id', Auth::user()->id)->pluck('company_id');
            $companies = Company::where('parent_company', $main_company)->orWhere('id', $main_company)->orWhere('owner_id', Auth::user()->id)->pluck('id');

            return Company::where(function ($query) use ($additional_companies, $companies) {
                $query->whereIn('id', $companies);
                $query->orWhereIn('id', $additional_companies);
            })->where('verified', Company::COMPANY_VERIFIED)->where(function($query){
                $query->whereNull('active_until');
                $query->orWhere('active_until', '>', \Illuminate\Support\Carbon::now());
            })->with('parent', 'membership')->orderBy('title')->get();
        }
    }

    /**
     * Returns company list values
     *
     */
    public function listValues(){

        $industries = Industry::all()->pluck('code','id');
        foreach ($industries as $key => $trans){
            $data['industries'][]= ['id' => $key,'title'=>trans('custom.industry_values.'.$trans)];
        }
        $employee_number = config('employee_numbers');
        foreach ($employee_number  as $key => $title){
            $data['employee_number'][]= ['id' => $key,'title'=>$title];
        }
        $specializations = Specialization::all()->pluck('title','id');
        foreach ($specializations  as $key => $title){
            $data['specializations'][]= ['id' => $key,'title'=>$title];
        }

        return response()->json([
            'result' => true,
            'data' => $data,
        ]);
    }


    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|unique:companies,title',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors'=>$validator->errors()], 401);
        }
        $request->request->set('company_name', $request->get('title'));
        $user = Auth::user();
        $users_own_company = Company::where('owner_id', $user->id)->first();

        $request->request->set('parent_company', null);

        $request->request->set('owner_id', $user->id);
        $request->request->set('storage_used', 0);

        $company = Company::create($request->all());
        $company->specializations()->attach($request->get('specialization'));

//        CompanyStorageCreateJob::dispatch($user, $company)->delay(15);

        try {
            Company::manager()->initiateNextCloud($company, true);
            Mail::to($user->email)->send(new \App\Mail\CompanyStorageInviteMail($user,$company));

        } catch (\Exception $e) {
            Log::info('Failed to create NEXTCloud account ' . $e->getMessage());
        }

        if (empty($users_own_company)){
            $current_role = $user->role;
            $current_company_id = $user->company_id;
            AdditionalUserCompanies::create([
                'user_id' => $user->id,
                'company_id' => $current_company_id,
                'role' => $current_role,
            ]);
            if ($user->role != 'super_user') {
                $user->company_id = $company->id;
                $user->role = 'administrator';
                $user->save();
            }
        }

        return $company;
    }

    public function show($id, $action)
    {

        if ($action == 'edit') {
            $main_company = Auth::user()->company_id;
            $additional_companies = AdditionalUserCompanies::where('user_id', Auth::user()->id)->where('role', 'administrator')->pluck('company_id')->toArray();
            $companies = Company::where('parent_company', $main_company)->orWhere('id', $main_company)->orWhere('owner_id', Auth::user()->id)->pluck('id')->toArray();


            if (!in_array($id, $companies) && !in_array($id, $additional_companies) && Auth::user()->role != 'super_user') {
                $id = $main_company;
            }
        }

        $company = Company::where('id', $id)->with('parent')->with('specializations')
            ->with('industry')->with('projects')->first();

        $additional_users = AdditionalUserCompanies::where('company_id', $id)->get();
        $users_roles = [];
        foreach($additional_users as $item){
            $users_roles[$item->user_id] = $item->role;
        }

        $users = User::whereIn('id', array_keys ($users_roles))->orWhere('company_id', $id)->orWhere('id', $company->owner_id)->get();
        foreach ($users as $user) {
            if(array_key_exists($user->id, $users_roles)){
                $user->role = $users_roles[$user->id];
            }
        }
        $company->users = $users;

        $company->verification_fields_list = $company->verification_fields_list;
        $company->employees_number_value = $company->employees_number_value;


        $admins = Company::manager()->getAllAdminsIds($id);
        $locales = [];
        foreach(User::whereIn('id', $admins) as $admin){
            $locales[] = $admin->locale;
        }
        $locales[] =  $company->owner->locale??'en';
        $company->owner_locale = implode(', ', array_unique($locales));


        if (Auth::user()->company_id == $company->id && Auth::user()->id == $company->owner_id && $company->verified == Company::COMPANY_NOT_VERIFIED){

            $settings = Auth::user()->settings;
            if (empty($settings) || empty($settings->data['skip_verification_intro'])) {
                $company->show_verify_popup = 1;
            }
        }

        return $company;
    }

    /**
     * Sends verification request to superadmin.
     *
     */
    public function verifyRequest($id)
    {
        $company = Company::where('id', $id)->first();

        $settings = $company->owner->settings;
        if (empty($settings)) {
            $settings = UserSettings::create([
                'user_id' => $company->owner_id,
            ]);
        }

        $sett_data['skip_verification_intro'] = 1;
        $settings->data = $sett_data;
        $settings->save();


        Mail::to(env('ADMINISTRATOR_EMAIL'))->send(new CompanyVerification([
            'type' => 'request',
            'lang' => 'en',
            'company_title' =>  $company->title,
            'company_id' =>  $company->id,
            'user_name' => Auth::user()->name]));
        return ['message' => 'success'];
    }

    /**
    * Sends message to the company's owner.
    *
    */
    public function messageAdmins(Request $request, $id)
    {
        $company = Company::where('id', $id)->first();

        $admins = Company::manager()->getAllAdminsIds($id);

        if (empty($company) || (empty($company->owner) && count($admins) == 0) ) {
            return response()->json(['errors' => 'Can\'t find company admins'], 401);
        }
        $admins = User::whereIn('id', $admins)->get();
        foreach($admins as $admin){
            Mail::to($admin->email)->send(new AdminNotification([
                'message' => $request->get('message'),
                'lang' => $admin->locale,
                'admin_name' => Auth::user()->name,
                'admin_email' => Auth::user()->email]));
        }
    }


    public function update(Request $request, $id)
    {
        $company = Company::findOrFail($id);
        if ($company->title != $request->get('title')) {
            $isset_company = Company::withTrashed()->where('title', $request->get('title'))->first();
            if (!empty($isset_company)) {
                return response()->json(['errors' => trans('custom.company_name_already_in_use', ['attribute' => 'title'])], 401);
            }
        }
        if ($company->verified != $request->get('verified')) {
            $user = Auth::user();
            if ($user->role == "super_user" && $user->company_id != $id && !empty($company->owner)) {
                if ($request->get('verified') == Company::COMPANY_VERIFIED) {
                    Mail::to($company->owner->email)->send(new CompanyVerification([
                        'type' => 'approved',
                        'company_title' => $company->title,
                        'lang' => $company->owner->locale,
                    ]));
                    if (Project::where('company_id', $company->id)->count() == 0 && AdditionalUserCompanies::where('user_id', $company->owner_id)->count() == 0
                        && Company::where('owner_id', $company->owner_id)->count() == 1) {
                        CopyProject::dispatch($company->id, env('DEMO_PROJECT_ID'));
                        ProjectVisibility::where('user_id', $company->owner_id)->where('project_id', env('DEMO_PROJECT_ID'))->delete();
                    }
                    if(is_null($company->active_until)){
                        Mail::to($company->owner->email)->send(new TrialNotification(['lang' => $company->owner->locale]));
                        $company->active_until = Carbon::now()->addDays(30);
                        $company->save();
                    }
                } else {
                    Mail::to($company->owner->email)->send(new CompanyVerification([
                        'type' => 'cancelled',
                        'company_title' => $company->title,
                        'lang' => $company->owner->locale,
                        'registered_data' => Carbon::parse($company->created_at)->format('Y-m-d'),
                    ]));
                }
            }
        }

        if ($company->owner_id == null && $request->get('owner_id') > 0) {
            $request->request->set('parent_company', null);

            /*save admin visibilities by parent relation from the old logic, deleting parent relation */
            $company_admins = User::where('company_id', $company->id)->where('role', 'administrator')->where('id', '!=', $request->get('owner_id'))->get();
            foreach (Company::where('parent_company', $company->id)->get() as $child_company) {
                if ($child_company->owner_id == null) {
                    $child_company->owner_id = $request->get('owner_id');
                    $child_company->parent_company = null;
                    $child_company->save();
                    foreach ($company_admins as $company_admin) {
                        if (AdditionalUserCompanies::where('company_id', $child_company->id)
                                ->where('user_id', $company_admin->id)
                                ->where('role', 'administrator')->count() == 0) {
                            AdditionalUserCompanies::create([
                                'company_id' => $child_company->id,
                                'user_id' => $company_admin->id,
                                'role' => 'administrator',
                            ]);
                        }
                    }
                }
            }
            $owner = User::where('id', $request->get('owner_id'))->first();
            if ($owner && $owner->company_id == $company->id && !in_array($owner->role, ['super_user', 'administrator'])){
                $owner->role = 'administrator';
                $owner->save();
            }

        }

        $company->update($request->all());
        $company->specializations()->sync($request->get('specialization'));

        return $company;
    }

    public function destroy($id)
    {
        Company::manager()->delete($id);

        return ['message' => 'success'];
    }

    public function destroy_user($id, $user_id)
    {
        $user = User::find($user_id);
        $company = Company::find($id);

        if ($user) {
            if ($user->company_id == $id) {
                $user->delete();
            } else {
                if(AdditionalUserCompanies::where(['company_id' => $id, 'user_id' => $user_id])->count() > 0){
                    AdditionalUserCompanies::where(['company_id' => $id, 'user_id' => $user_id])->delete();
                    Mail::to($user->email)->send(new EntityAccessDenied([
                        'entity' => 'company',
                        'company_title' => $company->title,
                        'admin_email' => $company->owner->email,
                        'user_name' => Auth::user()->name,
                        'lang' => $company->owner->locale]));
                }
            }
        }

        return ['message' => 'success'];
    }

    public function leaveCompany(Request $request, $id)
    {
        $user = Auth::user();

        $company = Company::find($id);

        AdditionalUserCompanies::where([
            'company_id' => $id,
            'user_id' => $user->id,
        ])->delete();

        $admins = User::whereIn('id', Company::manager()->getAllAdminsIds($id))->get();
        foreach ($admins as $admin) {
            Mail::to($admin->email)->send(new UserLeftEntity([
                'entity' => 'company',
                'company_title' => $company->title,
                'user_name' => Auth::user()->name,
                'lang' => $admin->locale]));
        }

        User::manager()->notificate($admins, [
            'title' => 'attention__',
            'message' => 'user_left_the_project_notification',
            'addition_vars' => [
                'user_name' => $user->name,
                'company_title' => $company->title,
            ]
        ]);

        return [
            'message' => 'success',
            'description' =>  trans('custom.you_have_left_the_company', ['company_title' => $company->title, 'admin_email'=> $company->owner->email]),
        ];
    }

    public function invite(Request $request, $id)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required',
            'role' => 'required'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors'=>$validator->errors()], 401);
        }

        if (!filter_var($request->get('email'), FILTER_VALIDATE_EMAIL)) {
            return response()->json(['errors'=>trans('validation.email', ['attribute' => 'email'])], 401);
        }

        $user = User::where('email', $request->get('email'))->first();
        $role = $request->get('role');

        if ($user) {
            if ($user->company_id != $id) {
                AdditionalUserCompanies::create([
                    'user_id' => $user->id,
                    'company_id' => $id,
                    'role' => $role
                ]);
            }

            $company = Company::find($id);

            Mail::to($request->get('email'))->send(new InviteToCompanyNotify([
                'email' => $request->get('email'),
                'company_title' => $company->title,
                'company_id' => $id,
                'sender_name' => Auth::user()->name,
                'sender_email' => Auth::user()->email,
                'lang'=> $user->locale,
                'role' => $role,
                'type' => 'existing_user',
            ]));

            try {
                Mail::to($user->email)->send(new \App\Mail\CompanyStorageInviteMail($user,$company));
            } catch (\Exception $e) {
                Log::info('Failed to create NEXTCloud account ' . $e->getMessage());
            }

        } else {

            Invitation::create([
                'email' => $request->get('email'),
                'company_id' => $id,
                'role' => $role,
            ]);

            Mail::to($request->get('email'))->send(new InviteToCompanyNotify([
                'email' => $request->get('email'),
                'company_title' => Company::find($id)->title,
                'company_id' => $id,
                'sender_name' => Auth::user()->name,
                'sender_email' => Auth::user()->email,
                'lang'=> 'en',
                'type' => 'new_user']));
        }

        return ['message' => 'success'];
    }

    public function change_user_role(Request $request, $id, $user_id)
    {
        $additional_role  = AdditionalUserCompanies::where(['company_id' => $id, 'user_id' => $user_id])->first();

        if (!empty($additional_role)){
            $additional_role->update(['role' => $request->get('role')]);
        } else {
            $user = User::where('id', $user_id)->where('company_id', $id)->first();
            if (!empty($user)){
                $user->role = $request->get('role');
                $user->save();
            } else{
                return response()->json(['errors'=>trans('custom.error_occured')], 401);
            }
        }

        return ['message' => 'success'];
    }

    /**
     * Searches companies by keyword.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function searchCompany(Request $request){

        $companies = Company::where('title', 'like', '%' . $request->get('term') . '%')->take($request->get('limit'))->get();

        return response()->json([
            'result' => true,
            'data' => json_encode($companies),
        ]);
    }

    public function deleteTempFile(Request $request)
    {
        $path = $request->get('path', false);
        $webdav = Session::get('web_dav');
        if ($path && !empty($webdav) && $webdav->has($path)){
            $webdav->delete($path);
            return ['message' => 'success'];
        } else {
            return response()->json(['errors'=>trans('custom.error_occured')], 403);
        }

    }

    public function moveTempFile(Request $request)
    {
        $path = $request->get('path', false);
        $project_id = $request->get('project_id', false);
        $delete_source = $request->get('delete_source', true);
        $project = Project::find($project_id);
        $webdav = Session::get('web_dav');
        $files_list  = Company::manager()->getTemporaryFiles(Auth::user()->company_id && !empty($webdav));
        if ($path && !empty($project) && array_key_exists($path, $files_list)){
            if($project->company_id == Auth::user()->company_id && !$delete_source) {
                $membership_limits = Company::manager()->checkAddingResult($project->company_id, 'space', intval($webdav->has($path)['size']));
                if (!$membership_limits['result']) {
                    return response()->json($membership_limits, 403);
                }
            }

            $filename = pathinfo($path, PATHINFO_BASENAME);
            $filepath ='/uploads/documents/' . $project_id . '/';

            if (Storage::disk('local')->exists($filepath.$filename)) {
                $filename = pathinfo($filename, PATHINFO_FILENAME) . '-' . uniqid() . '.' . pathinfo($path, PATHINFO_EXTENSION);
            }
            Storage::disk('local')->put($filepath . $filename, $webdav->read($path)['contents']);
            if($delete_source){
                $webdav->delete($path);
            }
            return response()->json([
                'result' => true,
                'new_file' => $filepath.$filename,
            ]);

        } else {
            return response()->json(['errors'=>trans('custom.error_occured')], 403);
        }

    }


    public function moveTempFileGallery(Request $request)
    {
        $path = $request->get('path', false);
        $project_id = $request->get('project_id', false);
        $folder_id = $request->get('folder_id', null);
        $folder_id = $folder_id != 0 ?: null;
        $delete_source = $request->get('delete_source', true);
        $project = Project::find($project_id);
        $webdav = Session::get('web_dav');
        $files_list  = Company::manager()->getTemporaryFiles(Auth::user()->company_id);
        if ($path && !empty($project) && array_key_exists($path, $files_list)){
            if ($project->company_id == Auth::user()->company_id && !$delete_source) {
                $membership_limits = Company::manager()->checkAddingResult($project->company_id, 'space', intval($webdav->has($path)['size']));
                if (!$membership_limits['result']) {
                    return response()->json($membership_limits, 403);
                }
            }

            $filename = pathinfo($path, PATHINFO_BASENAME);
            $filepath ='/images/';

            if (Storage::disk('local')->exists($filepath . $filename)) {
                $filename = pathinfo($filename, PATHINFO_FILENAME) . '-' . uniqid() . '.' . pathinfo($path, PATHINFO_EXTENSION);
            }

            Storage::disk('local')->put($filepath . $filename, $webdav->read($path)['contents']);
            if ($delete_source){
                $webdav->delete($path);
            }
            ProjectGalleryImage::create([
                'project_id' => $project_id,
                'url' => $filepath . $filename,
                'folder_id' => $folder_id,
            ]);
            return ['message' => 'success'];

        } else {
            return response()->json(['errors'=>trans('custom.error_occured')], 403);
        }

    }

}
