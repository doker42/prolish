<?php
declare(strict_types=1);

namespace App\Domain\Project;


use App\Foundation\Bridge\Laravel\UpTrait;
use App\Jobs\RecalculateCompany;
use App\Models\Company;
use App\Models\Notification;
use App\Models\NotificationsUser;
use App\Models\Project;
use App\Models\ProjectGalleryImage;
use App\Models\ProjectItem;
use App\Models\ProjectVisibility;
use App\Models\TransferRequest;
use App\Models\User;
use App\Models\UserFavouriteProject;
use http\Encoding\Stream\Inflate;
use Illuminate\Database\Eloquent\Collection;
use test\Mockery\ReturnTypeObjectTypeHint;

class ProjectManager
{
    use UpTrait;

    public function item(): ItemManager
    {
        return ItemManager::up();
    }

    public function gallery(): GalleryManager
    {
        return GalleryManager::up();
    }

    public function deleteAndNotify(int $project_id):void
    {
        $this->notificate($project_id, 'project_named', 'project_named_been_deleted');
        $this->delete($project_id);

    }

    public function copyDemoProject(int $project_id, int $company_id): void
    {
        $target_project = Project::where('id', $project_id)->first();

        $company = Company::where('id', $company_id)->first();

        if (!empty($target_project) && !empty($company)) {
            $new_project = $this->copyProject($project_id, $company_id);
        }
    }

    public function copyProject(int $target_project_id, int $company_id): Project
    {
        $target_project = Project::where('id', $target_project_id)->first();
        $new_project = $target_project->replicate();
        $new_project->company_id = $company_id;
        $new_project->description = $target_project->description;
        $new_project->save();


        $project_items_ids = ProjectItem::where('project_id', $target_project->id)->pluck('id');

        foreach ($project_items_ids as $item_id) {
            $this->item()->copyItem($item_id, $new_project->id);
        }

        $gallery_images = ProjectGalleryImage::where('project_id', $target_project->id)->pluck('id');
        foreach ($gallery_images as $image_id) {
            $this->gallery()->copyImage($image_id, $new_project->id);
        }

        return $new_project;

    }

    public function delete(int $project_id):void
    {
        $project = Project::withTrashed()->where('id', $project_id)->first();

        $company = Company::find($project->company_id);

        foreach ($project->contacts() as $contact) {
            $contact->delete();
        }

        foreach ($project->gallery_folders as $galleryfolder) {
            $this->gallery()->deleteFolder($project_id, $galleryfolder->id);
        }

        foreach ($project->gallery_images as $gallery_image) {
            $this->gallery()->deleteImage($gallery_image->id);
        }

        foreach ($project->items() as $item) {
            $this->item()->delete($project_id, $item->id);
        }

        foreach ($project->visibilities() as $visibility) {
            $visibility->delete();
        }

        UserFavouriteProject::where('project_id', $project_id)->delete();

        TransferRequest::where('project_id', $project_id)->delete();

        $favourities = UserFavouriteProject::where('project_id', $project_id)->get();

        foreach ($favourities as $faveourite) {
            $faveourite->delete();
        }

        $project_image = $project->image;

        $project->forceDelete();

        if(!empty($company)) {
            RecalculateCompany::dispatch($company);
        }

        $demo_project = Project::where('id', env("DEMO_PROJECT_ID"))->first();

        if($project_image != Project::DEFAULT_LOGO && !empty($demo_project) && $demo_project->image != $project_image){
            unlink(public_path(substr($project_image, 1)));
        }

    }

    /*
     * Returns all users related to the project
     *
     * @param  $project_id
     */
    public function getProjectUsers(int $project_id):Collection
    {
        $project = Project::withTrashed()->where('id', $project_id)->first();

        if (empty($project)) {
            return collect();
        }

        $users_notifications_array = [];

        foreach ($project->company->users as $user) {
            if (!(in_array($user->id, $users_notifications_array))) {
                $users_notifications_array[] = $user->id;
            }
        }

        foreach ($project->contacts as $contact) {
            $user = User::where('email', $contact->email)->first();
            if (!empty($user) && !(in_array($user->id, $users_notifications_array))) {
                $users_notifications_array[] = $user->id;
            }
            $contact->delete();
        }

        foreach ($project->contacts as $contact) {
            $user = User::where('email', $contact->email)->first();
            if (!empty($user) && !(in_array($user->id, $users_notifications_array))) {
                $users_notifications_array[] = $user->id;
            }
        }

        $projectVisibilities = ProjectVisibility::where('project_id', $project_id);

        foreach ($projectVisibilities as $visibility_item) {
            if (!(in_array($visibility_item->user_id, $users_notifications_array))) {
                $users_notifications_array[] = $visibility_item->user_id;
            }
        }

        $users = User::whereIn('id', $users_notifications_array)->get();

        return $users;

    }

    /*
    * Creates Notification for all related users for project
    *
    * @param  $project_id
    * @param $title
    * @param $message
    */
    public function notificate(int $project_id, string $title, string $message):void
    {

        $project = Project::withTrashed()->where('id', $project_id)->first();

        $users_to_notificate = self::getProjectUsers($project_id);

        User::manager()->notificate($users_to_notificate, [
            'title' => $title,
            'message' => $message,
            'addition_vars' => [
                'project_name' => $project->title
            ]
        ]);
    }
}