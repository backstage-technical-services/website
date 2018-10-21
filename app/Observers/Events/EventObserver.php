<?php

namespace App\Observers\Events;

use App\Logger;
use App\Models\Events\Event;
use App\Observers\ModelObserver;

class EventObserver extends ModelObserver
{
    public function created(Event $event)
    {
        Logger::log('event.create', true, $this->getCreatedAttributes($event));
    }

    public function updated(Event $event)
    {
        Logger::log('event.edit', true, $this->getUpdatedAttributes($event));
    }

    public function deleted(Event $event)
    {
        Logger::log('event.delete', true, $this->getDeletedAttributes($event));
    }
}