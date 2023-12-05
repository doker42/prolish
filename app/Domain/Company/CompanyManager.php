<?php
declare(strict_types=1);

namespace App\Domain\Company;


use App\Domain\Billing\SubscriptionManager;
use App\Domain\Project\ProjectManager;
use App\Domain\User\UserManager;
use App\Foundation\Bridge\Laravel\UpTrait;
use App\Mail\CommonEmail;
use App\Mail\CompanyDelete;
use App\Models\AdditionalUserCompanies;
use App\Helpers\NextCloudHelper;
use App\Models\Company;
use App\Models\Invitation;
use App\Models\Membership;
use App\Models\Project;
use App\Models\ProjectVisibility;
use App\Models\CompanySubscriptions;
use App\Models\User;
use Sabre\DAV\Client as WebDAVClient;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use League\Flysystem\WebDAV\WebDAVAdapter;

class CompanyManager
{
    use UpTrait;

    public function project(): ProjectManager
    {
        return ProjectManager::up();
    }

    public function user(): UserManager
    {
        return UserManager::up();
    }

    public function subscription(): SubscriptionManager
    {
        return SubscriptionManager::up();
    }

    public function getAllAdminsIds(int $company_id): array
    {

        $company = Company::where('id', $company_id)->first();

        $admins = User::where('role', 'administrator')
            ->where(function ($query) use ($company_id, $company) {
                $query->where('company_id', $company_id);
          //      $query->orWhere('company_id', $company->parent_company);
                $query->orWhere('id', $company->owner_id);
            })->pluck('id');
        $additional_admins = AdditionalUserCompanies::where('role', 'administrator')
            ->where(function ($query) use ($company_id, $company) {
                $query->where('company_id', $company_id);
            })->pluck('user_id');

        return array_merge($admins->toArray(), $additional_admins->toArray());

    }

    public function delete(int $company_id): void
    {
        $company = Company::withTrashed()->where('id', $company_id)->first();

        if (!empty($company)) {
            $this->deleteCompanySubscriptions($company);

            $this->deleteCompanyUsers($company_id);

            $this->notificate($company_id);

            $company_projects_ids = Project::where('company_id', $company_id)->pluck('id');

            ProjectVisibility::whereIn('project_id', $company_projects_ids)->orWhere('company_id', $company_id)->delete();

            AdditionalUserCompanies::where('company_id', $company_id)->delete();

            foreach ($company->projects() as $project) {
                $this->project()->delete($project->id, false);
            }

            $company->specializations()->sync([]);

            $company->forceDelete();
        }

    }

    /*
    * Deletes companies users
    *
    * @param  $company_id
     */

    public function deleteCompanySubscriptions(Company $company):void
    {
        $subscr_ids = CompanySubscriptions::where('company_id', $company->id)->pluck('id');
        if($subscr_ids->count() > 0) {
            foreach ($subscr_ids->count() as $item) {
                if (!$this->subscription()->delete($item)) {
                    Mail::to(env('ADMINISTRATOR_EMAIL'))->send(new CommonEmail(['message' => 'Failed to delete subscription 
                on delete company '.$company->title, 'title' => 'Stripe Account ALERT']));
                }
            }
        }

    }

    /*
    * Deletes companies users
    *
    * @param  $company_id
    */
    public function deleteCompanyUsers(int $company_id):void
    {
        $company = Company::withTrashed()->where('id', $company_id)->first();
        if (!empty($company)) {
            $users = User::where('company_id', $company_id)->get();
            foreach ($users as $user) {
                if (Auth::user()->role == 'super_user') {
                    if ($company->owner_id == $user->id) {
                        $users_own_company = Company::where('owner_id', $user->id)->where('id', '!=', $company_id)->first();
                        if (!empty($users_own_company)) {
                            $user->company_id = $users_own_company->id;
                            $user->save();
                            Mail::to($user->email)->send(new CompanyDelete(['company_name' => $company->title, 'lang' => $user->locale]));
                        } else {
                            Mail::to($user->email)->send(new CompanyDelete(['company_name' => $company->title, 'account_deleted' => 1, 'lang' => $user->locale]));
                            $this->user()->delete($user->id);
                        }
                    }
                }
                $users_own_company = Company::where('owner_id', $user->id)->first();
                if (!empty($users_own_company)) {
                    $user->company_id = $users_own_company->id;
                    $user->save();
                } else {
                    $additional_access = AdditionalUserCompanies::where('user_id', $user->id)->first();
                    if (!empty($additional_access)) {
                        $user->company_id = $additional_access->company_id;
                        $user->role = $additional_access->role;
                        $user->save();
                        $additional_access->delete();
                    } else {
                        Mail::to($user->email)->send(new CompanyDelete(['company_name' => $company->title, 'account_deleted' => 1, 'lang' => $user->locale]));
                        $this->user()->delete($user->id);
                    }
                }

            }
        }

    }



    /*
    * Returns all users related to the company
    *
    * @param  $company_id
    */
    public function getCompanyUsers(int $company_id):array
    {
        $company = Company::withTrashed()->where('id', $company_id)->first();

        if (empty($company)) {
            return [];
        }
        $users_data = [];
        $additional_companies = AdditionalUserCompanies::where('company_id', $company_id)->pluck('user_id');
        $visibilities = ProjectVisibility::where('company_id', $company_id)->pluck('user_id');
        $users = User::withTrashed()->where('company_id', $company_id)->orWhereIn('id', $visibilities)->orWhereIn('id', $additional_companies)->get();

        foreach($users as $user){
            $locale = 'en';
            $settings = $user->settings;
            if (!empty($settings)){
                $data = $settings->data;
                if (isset($data['locale'])){
                    $locale = $data['locale'];
                }
            }
            $users_data[$user->email] = $locale;
        }

        return $users_data;
    }

    /*
    * Creates Emails for all related users for company
    *
    * @param  $company_id
    */
    public function notificate(int $company_id):void
    {

        $company = Company::withTrashed()->where('id', $company_id)->first();

        foreach($this->getCompanyUsers($company_id) as $email => $locale) {
            Mail::to($email)->send(new CompanyDelete(['company_name' => $company->title, 'lang' => $locale]));
        }

    }

    /*
     * returns left resources according to membership limits
     */

    public function getLimitsInfo(int $company_id): array
    {
        $company = Company::find($company_id);
        if (!$company){
            return [];
        }
        $membership = Membership::find($company->membership_id);
        $projects_count = Project::where('company_id', $company)->count();
        $managers = User::whereIn('role', ['administrator', 'manager'])->where('company_id', $company_id)->where('id', '!=', $company->owner_id)->count();
        $visitors = User::where('role', 'visitor')->where('company_id', $company_id)->count();
        $shared_managers = AdditionalUserCompanies::whereIn('role', ['administrator', 'manager'])->where('company_id', $company_id)->count();
        $shared_visitors = AdditionalUserCompanies::where('role', 'visitor')->where('company_id', $company_id)->count();
        $invites_managers = Invitation::whereIn('role', ['administrator', 'manager'])->where('company_id', $company_id)->count();
        $invites_visitors = Invitation::where('role', 'visitor')->where('company_id', $company_id)->count();
        return [
            'projects_left' => $membership->projects_limit > 0 ? $membership->projects_limit - $projects_count : 1000,
            'managers_left' => $membership->managers_limit > 0 ? $membership->managers_limit - $managers - $shared_managers - $invites_managers : 1000,
            'visitors_left' => $membership->visitors_limit > 0 ? $membership->visitors_limit - $visitors - $shared_visitors - $invites_visitors : 1000,
            'space_left' => ($membership->size  - $company->storage_used) * 1024 * 1024,
            'base_limits' => [
                'projects_limit' => $membership->projects_limit,
                'managers_limit' => $membership->managers_limit,
                'visitors_limit' => $membership->visitors_limit,
                'space_limit' => $membership->size/1000,
            ],
            'is_current_owner' => Auth::check()?Auth::user()->id == $company->owner_id:true,
            'owner_email' =>  $company->owner->email,
            'company_title' => $company->title,
        ];
    }
    /*
     * checks does adding of new entities broke the limits
     */

    public function checkAddingResult($company_id, $entity, $num = 1): array
    {
        $company = Company::find($company_id);
        $limits_info = $this->getLimitsInfo(intval($company_id));
        $result = true;

        switch ($entity) {
            case 'project':
                $result = $limits_info['projects_left'] - $num >= 0;
                break;

            case 'manager':
                $result = $limits_info['managers_left'] - $num >= 0;
                break;

            case 'visitor':
                $result = $limits_info['visitors_left'] - $num >= 0;
                break;
            case 'space':
                $result = $limits_info['space_left'] - $num >= 0;
                break;
            default:
                break;
        }

        if ($result){
            return ['result' => true];
        } else {
            if ($company->membership_id == Membership::FREE_PACKAGE_ID){
                SupportLog::create([
                    'user_name' => Auth::check()?Auth::user()->name:'',
                    'user_email' => Auth::check()?Auth::user()->email:'',
                    'company_name' => $company->title,
                    'company_owner_email' => $company->owner->email,
                    'company_owner_name' => $company->owner->name,
                    'trans' => 'limit_trigerred_by_'.$entity,
                ]);
            }


            $result =  ['result' => false];
            $result = array_merge($result, $limits_info['base_limits']);
            $result['error_message'] = $limits_info['is_current_owner']? trans('custom.if_you_need_to_expand_the_options_in_your_plan_please_choose_another_one')." <a target='_blank' href='https://my3d.cloud/#data-plans'>My3d.cloud/data-plans</a>.":
                trans('custom.if_you_need_to_expand_the_options_please_contact_account_owner')." (<a target='_blank' href='mailto:".$limits_info['owner_email']."'>".$limits_info['owner_email']."</a>).";
            return $result;
        }


    }

    /**
     * Crates NextCloud and WebDav directory for the company
     */
    public function initiateNextCloud(Company $company, $put_adapter_to_session = false): array
    {
        $NextCloudHelper = new NextCloudHelper();
        $data = $NextCloudHelper->createTempFolder($company);

        if ($data['result']){
            $company->storage_pass = $data['password'];
            $free_mb_space = $company->membership->size - $company->storage_used;
            $NextCloudHelper->updateTempFolderLimit($company, (int) $free_mb_space);
            $company->save();

            return [
                'password' => $data['password']
            ];
        }
        if ($put_adapter_to_session && Auth::check()){
            $this->getWebDavAdapter($company);
        }
        return [];
    }
    /**
    * Returns company's webDavAdapter
    */
    public function getWebDavAdapter(Company $company):WebDAVAdapter{
        if(Session::get('web_dav_company_id') == $company->id && Session::exists('web_dav')){
            return Session::get('web_dav');
        } else {
            $config['baseUri'] = env('NEXTCLOUD_API_URL');
            $config['userName'] = $company->temporary_folder;
            $config['password'] = $company->storage_pass;
            $subfix = env('NEXTCLOUD_API_SUBFIX');
            $config['pathPrefix'] = $subfix . '/' . 'remote.php/dav/files/'.$company->temporary_folder;
            $client = new WebDAVClient($config);
            $adapter = new WebDAVAdapter($client, $config['pathPrefix'], $config);
            Session::put('web_dav', $adapter);
            Session::put('web_dav_company_id', $company->id);
            return $adapter;
        }
    }
    /**
     * Returns company's temporary files array
     */
    public function getTemporaryFiles(int $company_id):array
    {
        if(Company::where('id', $company_id)->count() > 0){
            $webdav = Session::get('web_dav');
            if (empty($webdav)){
                return [];
            }

            $files = $webdav->listContents();

            $files_to_return = [];
            foreach($files as $file){
                if ($file['type'] == 'file'){
                    $files_to_return[$file['path']] = [
                        'size' => number_format($file['size'] / 1048576, 2, ".", ""),
                        'created_at' => date('Y-m-d', intval($file['timestamp'])),
                        'title' => pathinfo($file['path'], PATHINFO_BASENAME),
                        'ext' => pathinfo($file['path'], PATHINFO_EXTENSION),
                        'path' => $file['path'],
                    ];
                }
            }
            return $files_to_return;
        } else {
            return [];
        }
    }
}
