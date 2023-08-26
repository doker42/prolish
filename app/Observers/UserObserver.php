<?php

namespace App\Observers;

use App\Models\AdditionalUserCompanies;
use App\Models\ProjectLog;
use App\Models\User;
use App\Models\Project;
use App\Models\ProjectVisibility;
use App\Models\Invitation;
use Illuminate\Support\Facades\Auth;

class UserObserver
{
    /**
     * Handle the user "created" event.
     *
     * @param \App\Models\User $user
     * @return void
     */
    public function created(User $user)
    {
        $project = Project::where('id', env("DEMO_PROJECT_ID"))->first();
        if (!empty($project)) {
            ProjectVisibility::create([
                'role' => 'visitor',
                'user_id' => $user->id,
                'project_id' => $project->id,
            ]);
        }

        foreach ($invitations = Invitation::where('email', $user->email)->get() as $invite) {
            if ($invite->project_id > 0) {

                ProjectVisibility::create([
                    'project_id' => $invite->project_id,
                    'user_id' => $user->id,
                    'role' => $invite->role
                ]);

                ProjectLog::create([
                    'user_id' => $user->id,
                    'project_id' => $invite->project_id,
                    'trans' => 'logs_visibility_add',
                    'data' => [
                        'user_id' => $user->id,
                        'email' => $user->email,
                        'role' => $invite->role
                    ]
                ]);

            }

            if ($invite->company_id > 0) {
                AdditionalUserCompanies::create([
                    'user_id' => $user->id,
                    'company_id' => $invite->company_id,
                    'role' => $invite->role
                ]);
            }
            $invite->delete();
        }

    }

}
