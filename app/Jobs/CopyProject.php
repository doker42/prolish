<?php

namespace App\Jobs;

use App\Domain\Project\ProjectManager;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class CopyProject implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $project_id;
    protected $company_id;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(int $company_id, int $project_id)
    {
        $this->project_id = $project_id;
        $this->company_id = $company_id;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        $this->projectManager()->copyProject($this->project_id, $this->company_id);

    }

    public function projectManager(): ProjectManager
    {
        return ProjectManager::up();
    }

}
