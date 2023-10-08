<?php

namespace App\Http\Controllers\Auth;

use App\Jobs\CompanyStorageCreateJob;
use App\Mail\VerifyMail;
use App\Mail\WelcomeEmail;
use App\Models\Company;
use App\Models\Membership;
use App\Models\User;
use App\Http\Controllers\Controller;
use App\Models\UserSettings;
use App\Models\VerifyUser;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\RegistersUsers;
use App\Mail\ApproveCompanyJoin;
use App\Mail\JoinCompanyResponse;

class RegisterController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Register Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users as well as their
    | validation and creation. By default this controller uses a trait to
    | provide this functionality without requiring any additional code.
    |
    */

    use RegistersUsers;

    /**
     * Where to redirect users after registration.
     *
     * @var string
     */
    protected $redirectTo = '/#/memberships';

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest');
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array  $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array  $data
     * @return \App\Models\User
     */
    protected function create(array $data)
    {
        $membership = Membership::where('id', Membership::FREE_PACKAGE_ID)->first();

        $company_id = 0;
        if (isset($data['company_id']) && $data['company_id'] > 0) {
            $company_id = $data['company_id'];
        } else {
            $isset_company = Company::where('title', $data['company'])->first();
            if (!empty($isset_company)) {
                $company_id = $isset_company->id;
            }
        }

        if ($company_id > 0){

            /* User register with confirmation of the company admin*/

            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => 'visitor',
                'company_id' => $company_id,
                'verified' => 1,
            ]);

            VerifyUser::create([
                'user_id' => $user->id,
                'token' => str_random(40)
            ]);

            $settings = $user->settings;
            if (empty($settings)){
                $settings = UserSettings::create([
                    'user_id' => $user->id,
                ]);
                $sett_data['locale'] = Session::get('applocale');
                $settings->data = $sett_data;
                $settings->save();
            }

            Mail::to($user->email)->send(new VerifyMail($user));

        } else {

            /* Simple user register*/

            $company = Company::create([
                'title' => $data['company'],
                'status' => 1,
                'storage_used' => 0,
                'logo' => '/images/450x450.png',
                'parent_company' => null,
                'membership_id' => $membership->id,
            ]);

            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'role' => 'administrator',
                'company_id' => $company->id,
                'verified' => 1,
            ]);

            $settings = $user->settings;
            if (empty($settings)){
                $settings = UserSettings::create([
                    'user_id' => $user->id,
                ]);
                $sett_data['locale'] = Session::get('applocale');
                $settings->data = $sett_data;
                $settings->save();
            }

            $company->owner_id = $user->id;
            $company->save();

            VerifyUser::create([
                'user_id' => $user->id,
                'token' => str_random(40)
            ]);

            Mail::to($user->email)->send(new VerifyMail($user));

            CompanyStorageCreateJob::dispatch($user, $company)->delay(15);


//            try {
//                Company::manager()->initiateNextCloud($company, true);
//
//                Mail::to($user->email)->send(new \App\Mail\CompanyStorageInviteMail($user,$company));
//
//            } catch (\Exception $e) {
//                Log::info('Failed to create NEXTCloud account ' . $e->getMessage());
//            }
        }

        $user->job_title = $data['job_title'];
        $user->save();

        return $user;
    }


    public function verifyUser($token)
    {
        $verifyUser = VerifyUser::where('token', $token)->first();
        if(isset($verifyUser) ){
            $user = $verifyUser->user;

            switch ($user->verified) {
                case User::VERIFIED_NOT_VERIFIED:
                    if ($user->role == 'visitor'){
                        $user->verified = User::VERIFIED_AWAITS_APPROVE;
                        $user->save();
                        $status = trans('custom.company_join_validation.wait_admin_approve', ['company_name' => $user->belongToCompany->title]);
                        $admin = User::where('company_id', $user->company_id)->where('role', 'administrator')->first();
                        if (empty($admin)){
                            $company = Company::where ('id', $user->company_id)->first();
                            $admin = User::where('id', $company->owner_id)->first();
                        }

                        $settings = $admin->settings;
                        $locale = 'en';
                        if (!empty($settings)){
                            $data = $settings->data;
                            if (isset($data['locale'])){
                                $locale = $data['locale'];
                            }
                        }
                        Mail::to($admin->email)->send(new ApproveCompanyJoin($user, $locale));
                    } else {
                        $verifyUser->user->verified = 1;
                        $verifyUser->user->save();
                        $status = trans('custom.email_validation.verified');
                        Mail::to($user->email)->send(new WelcomeEmail($user));
                    }
                    break;
                case User::VERIFIED_VERIFIED:
                    $status = trans('custom.email_validation.already_verified');
                    break;
                case User::VERIFIED_AWAITS_APPROVE:
                    $status = trans('custom.company_join_validation.wait_admin_approve', ['company_name' => $user->belongToCompany->title]);
                    break;
                default:
                    $status = trans('custom.email_validation.cannot_verified');
                    break;

            };
        }else{
            return redirect('/login')->with('warning', trans('custom.email_validation.cannot_verified'));
        }

        return redirect('/login')->with('status', $status);
    }

    protected function registered(Request $request, $user)
    {
        $this->guard()->logout();
        return redirect('/login')->with('status', trans('custom.email_validation.sent'));
    }
}
