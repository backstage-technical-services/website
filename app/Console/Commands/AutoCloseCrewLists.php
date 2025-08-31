<?php

namespace App\Console\Commands;

use App\Models\Events\Event;
use Illuminate\Console\Command;

class AutoCloseCrewLists extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crew:auto-close';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Close any crew lists for completed events.';

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
        Event::past()
            ->where('events.crew_list_status', Event::CREW_LIST_OPEN)
            ->get()
            ->map(function ($event) {
                $event->update([
                    'events.crew_list_status' => Event::CREW_LIST_CLOSED,
                ]);
            });

        $this->info('Done.');
    }
}
