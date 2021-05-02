<?php

namespace App\Console;

use App\Console\Commands\AutoCloseCrewLists;
use App\Console\Commands\BackupDb;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

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
        $schedule->command('backup:run')
                 ->weekly()
                 ->then(
                     function () use ($schedule) {
                         $schedule->command('backup:clean');
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
