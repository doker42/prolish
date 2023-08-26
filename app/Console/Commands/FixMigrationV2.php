<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\Membership;
use App\Models\ProjectItem;
use App\Models\ProjectVisibility;
use App\Models\User;
use Illuminate\Console\Command;

class FixMigrationV2 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'migration_v2:fix';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Make changes for migration to v2';

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
        ProjectVisibility::where('role', '=', '')->update([
            'role' => 'visitor'
        ]);

        ProjectItem::where('status', 'success')->update([
            'status' => 'custom.item_status_success'
        ]);

        $membership = Membership::where('id', Membership::FREE_PACKAGE_ID)->first();

        if (!empty($membership)) {
            Company::all()->each(function($company) use ($membership) {
                $company->membership_id = $membership->id;
                $company->save();
            });
        }

        User::where('id', '>', 0)->update([
            'verified' => true
        ]);

    }
}
