<?php

namespace App\Console\Commands;

use App\Models\ProjectVisibility;
use App\Models\User;
use Illuminate\Console\Command;

class UpdateUsersRoles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'userroles:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Script updates users roles';

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
        $visitor_users = User::whereIn('role', ['user', 'user_documents', 'user_files'])->get();
        foreach ($visitor_users as $user){
            $user->role = 'visitor';
            $user->save();
        }
        echo $visitor_users->count().' users got role "Visitor"'.PHP_EOL;

        $manager_users = User::whereIn('role', ['manager_documents', 'manager_files'])->get();
        foreach ($manager_users as $user){
            $user->role = 'manager';
            $user->save();
        }
        echo $manager_users->count().' users got role "Manager"'.PHP_EOL;

        \DB::table('project_visibilities')->whereIn('role', ['user', 'user_documents', 'user_files'])->update([
            'role' => 'visitor',
        ]);

        \DB::table('project_visibilities')->whereIn('role', ['manager_files', 'manager_documents'])->update([
            'role' => 'manager',
        ]);

    }
}
