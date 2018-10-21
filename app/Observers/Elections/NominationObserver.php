<?php

namespace App\Observers\Elections;

use App\Logger;
use App\Models\Elections\Nomination;
use App\Observers\ModelObserver;

class NominationObserver extends ModelObserver
{
    public function created(Nomination $nomination)
    {
        Logger::log('election-nomination.create', true, $this->getCreatedAttributes($nomination));
    }

    public function updated(Nomination $nomination)
    {
        $attributes = $this->getUpdatedAttributes($nomination);

        if (isset($attributes['elected']) && $attributes['elected']) {
            Logger::log('election-nomination.elected', true, ['id' => $nomination->id]);
        }
    }

    public function deleted(Nomination $nomination)
    {
        Logger::log('election-nomination.delete', true, $this->getDeletedAttributes($nomination));
    }
}