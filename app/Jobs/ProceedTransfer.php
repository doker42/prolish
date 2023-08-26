<?php

namespace App\Jobs;

use App\Domain\Project\ProjectManager;
use App\Models\Company;
use App\Models\Project;
use App\Models\TransferRequest;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ProceedTransfer implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $transfer_id;
    protected $approver_id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(int $transfer_id, int $approver_id)
    {
        $this->transfer_id = $transfer_id;
        $this->approver_id = $approver_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $transfer = TransferRequest::find($this->transfer_id);
        $new_project = $this->projectManager()->copyProject($transfer->project_id, $transfer->company_id);
        if (!empty($new_project)){
            $base_project = Project::find($transfer->project_id);
            $base_company = Company::find($base_project->company_id);
            $new_company = Company::find($new_project->company_id);
            $old_admins = Company::manager()->getAllAdminsIds($base_project->company_id);
            $new_admins = Company::manager()->getAllAdminsIds($transfer->company_id);
            $approver_user = User::find($this->approver_id);
            $users = User::whereIn('id', $old_admins)->orWhereIn('id', $new_admins)->get();
            $transfer->delete();
            User::manager()->notificate($users, [
                'title' => 'transfer_succeed',
                'message' => 'transfer_project_approved_notification',
                'addition_vars' => [
                    'old_owner_company' => $base_company->title,
                    'project_name' => $base_project->title,
                    'new_owner_company' => $new_company->title,
                    'user_approved' => $approver_user->name,
                ]
            ]);
        }
    }

    public function projectManager(): ProjectManager
    {
        return ProjectManager::up();
    }

}
