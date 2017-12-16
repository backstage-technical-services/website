<?php

namespace App\Console;

use App\Models\Events\Event;
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
        //
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     *
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $this->closePastEventCrewLists($schedule);
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

    /**
     * Close the crew list for past events.
     *
     * @param \Illuminate\Console\Scheduling\Schedule $schedule
     */
    private function closePastEventCrewLists(Schedule $schedule)
    {
        $schedule->call(function () {
            Event::past()
                 ->where('events.crew_list_status', Event::CREW_LIST_OPEN)
                 ->get()
                 ->map(function ($event) {
                     $event->update([
                         'crew_list_status' => Event::CREW_LIST_CLOSED,
                     ]);
                 });
        })->daily();
    }
}
