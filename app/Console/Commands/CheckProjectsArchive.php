<?php

namespace App\Console\Commands;

use App\Models\Project;
use Illuminate\Console\Command;
use Carbon\Carbon;

class CheckProjectsArchive extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'projectarchive:check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {

        /*checking week before */
        $days23_ago = Carbon::now()->subDay(23);
        $days23_ago_minus_hour = Carbon::now()->subDay(23)->addHour(1);

        $projects = Project::withTrashed()->where('deleted_at', '>=', $days23_ago)->where('deleted_at', '<', $days23_ago_minus_hour)->get();

        foreach($projects as $project){
            Project::manager()->notificate($project->id, 'project_named', 'project_will_be_deleted_in_a_week');
        }

        /*checking day before */
        $days29_ago = Carbon::now()->subDay(29);
        $days29_ago_minus_hour = Carbon::now()->subDay(29)->addHour(1);

        $projects = Project::withTrashed()->where('deleted_at', '>=', $days29_ago)->where('deleted_at', '<', $days29_ago_minus_hour)->get();

        foreach($projects as $project){
            Project::manager()->notificate($project->id, 'project_named', 'project_will_be_deleted_in_a_day');
        }

        /*checking to delete */
        $days30_ago = Carbon::now()->subDay(30);
        $days30_ago_minus_one_hour = Carbon::now()->subDay(30)->addHour(1);

        $projects = Project::withTrashed()->where('deleted_at', '>=', $days30_ago)->where('deleted_at', '<', $days30_ago_minus_one_hour)->get();

        foreach($projects as $project){
            Project::manager()->deleteAndNotify($project->id);
        }

    }
}
