<?php
declare(strict_types=1);

namespace App\Domain\User;


use App\Foundation\Bridge\Laravel\UpTrait;
use App\Models\AdditionalUserCompanies;
use App\Models\Company;
use App\Models\Notification;
use App\Models\NotificationsUser;
use App\Models\Project;
use App\Models\ProjectVisibility;
use App\Models\User;
use App\Models\UserSettings;
use Illuminate\Database\Eloquent\Collection;

class UserManager
{
    use UpTrait;

    public function getUserRoles(int $user_id):array
    {
        $system_roles = array_keys(config('roles'));

        $roles = [];
        foreach(['company', 'project'] as $object){
                $roles[$object] = [];
        }

        $user = User::where('id', $user_id)->first();
        if (!$user){
            return $roles;
        }

        if($user->role == 'super_user'){
            $roles['is_super_user'] = true;
            return $roles;
        }

       $roles['shared_projects'] = [];
       $additional_companies = AdditionalUserCompanies::where('user_id', $user_id)->get();
       foreach($additional_companies as $add_company){
           $roles['company'][$add_company->company_id] = $add_company->role;
           $roles['additional_companies'][] = $add_company->company_id;
       }
        $companies = Company::where(function ($query) use ($user) {
            if ($user->role == 'administrator') {
                $query->where('parent_company', $user->company_id);
            }
        })->orWhere('owner_id', $user->id)
            ->orWhere('id', $user->company_id)
            ->pluck('id');

        foreach ($companies as $company_id) {
            $roles['company'][$company_id] = $user->role;
        }
        $company_shared_projects = ProjectVisibility::whereIn('company_id', array_keys($roles['company']))->whereNull('user_id')->get();
        foreach($company_shared_projects as $item){
            $roles['project'][$item->project_id] = $item->role;
            $roles['shared_projects'][] = $item->project_id;
        }
        $shared_projects = ProjectVisibility::where('user_id', $user->id)->get();
        foreach ($shared_projects as $visibility) {
            $roles['project'][$visibility->project_id] = $visibility->role;
            $roles['shared_projects'][] = $visibility->project_id;
        }
        $projects = Project::withTrashed()->whereIn('company_id', $companies)->orWhereIn('company_id', $additional_companies->pluck('company_id'))->get();

        foreach ($projects as $project) {
            if (!empty($roles['company'][$project->company_id])) {
                $roles['project'][$project->id] = $roles['company'][$project->company_id];
            }

        }
        return $roles;
    }

    public function delete(int $user_id):void
    {
        AdditionalUserCompanies::where('user_id', $user_id)->delete();
        ProjectVisibility::where('user_id', $user_id)->delete();
        UserSettings::where('user_id', $user_id)->delete();
        User::where('id', $user_id)->forceDelete();
    }

    /*
    * Creates Notification for users
    *
    * @param  $users
    * @param $message_opts
    */
    public function notificate(Collection $users, array $message_opts): void
    {
        $lanqueges = config('languages');
        $notification_params = [];
        foreach ($lanqueges as $key => $value) {
            $model_arrt = 'title_' . $key;
            $notification_params[$model_arrt] = trans('custom.' . $message_opts['title']??'', $message_opts['addition_vars']??[], $key);
            $model_arrt = 'content_' . $key;
            $notification_params[$model_arrt] = trans('custom.' . $message_opts['message']??'', $message_opts['addition_vars']??[], $key);
        }
        if (!is_array($users) && $users->count() > 0) {
            $notification = Notification::create($notification_params);
            $users->each(function ($user) use ($notification) {
                if ($user instanceof User) {
                    NotificationsUser::create([
                        'user_id' => $user->id,
                        'notification_id' => $notification->id
                    ]);
                }
            });

        }
    }




}