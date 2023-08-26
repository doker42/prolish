<?php

namespace App\Console;

use App\Console\Commands\Cleanup;
use App\Console\Commands\FixMigrationV2;
use App\Console\Commands\FixViewUrl;
use App\Console\Commands\ConvertMissingPotree;
use App\Console\Commands\MembershipPeriodsReminder;
use App\Console\Commands\RecalculateSizes;
use App\Console\Commands\Update3DViewerFiles;
use Illuminate\Console\Scheduling\Schedule;
use App\Console\Commands\CheckProjectsArchive;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        FixViewUrl::class,
        Cleanup::class,
        FixMigrationV2::class,
        ConvertMissingPotree::class,
        Update3DViewerFiles::class,
        RecalculateSizes::class,
        CheckProjectsArchive::class,
        MembershipPeriodsReminder::class
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Muted functions calls until required.
        /* $schedule->command('cleanup:run')->daily();
         $schedule->command('membership:charge')->hourly();*/
         $schedule->command('projectarchive:check')->hourly();
         $schedule->command('tokenlifetime:check')->hourly();
         $schedule->command('membershipperiod:remind')->hourly();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
