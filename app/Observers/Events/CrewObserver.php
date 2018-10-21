<?php

namespace App\Observers\Events;

use App\Logger;
use App\Models\Events\Crew;
use App\Observers\ModelObserver;

class CrewObserver extends ModelObserver
{
    public function updated(Crew $crew)
    {
        Logger::log('event-crew.updated', true, $this->getUpdatedAttributes($crew));
    }
}