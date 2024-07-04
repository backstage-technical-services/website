<?php

namespace App\Console;

use App\Console\Commands\AutoCloseCrewLists;
use App\Console\Commands\BackupDb;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Spatie\Backup\Commands\BackupCommand;
use Spatie\Backup\Commands\CleanupCommand;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        BackupDb::class,
        AutoCloseCrewLists::class,
        BackupCommand::class,
        CleanupCommand::class,
    ];
    
    /**
     * Define the application's command schedule.
     *
     * @param Schedule $schedule
     *
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command(AutoCloseCrewLists::class)->daily();
        $schedule->command(BackupDb::class)->daily();
        $schedule->command(BackupCommand::class)
                 ->weekly()
                 ->then(
                     function () use ($schedule) {
                         $schedule->command(CleanupCommand::class);
                     }
                 );
    }
    
    /**
     * Register the Closure based commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        require base_path('routes/console.php');
    }
}
