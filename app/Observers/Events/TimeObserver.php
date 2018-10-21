<?php

namespace App\Observers\Events;

use App\Logger;
use App\Models\Events\Time;
use App\Observers\ModelObserver;

class TimeObserver extends ModelObserver
{
    public function created(Time $time)
    {
        Logger::log('event-time.create', true, $this->getCreatedAttributes($time));
    }

    public function updated(Time $time)
    {
        Logger::log('event-time.edit', true, $this->getUpdatedAttributes($time));
    }

    public function deleted(Time $time)
    {
        Logger::log('event-time.delete', true, $this->getDeletedAttributes($time));
    }
}