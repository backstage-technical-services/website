<?php

namespace App\Console\Schedules;

use App\Models\Events\Event;

class CloseCrewLists
{
    public function __invoke()
    {
        Event::past()
             ->where('events.crew_list_status', Event::CREW_LIST_OPEN)
             ->get()
             ->map(function ($event) {
                 $event->update([
                     'crew_list_status' => Event::CREW_LIST_CLOSED,
                 ]);
             });
    }
}