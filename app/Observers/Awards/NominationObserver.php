<?php

namespace App\Observers\Awards;

use App\Logger;
use App\Models\Awards\Nomination;
use App\Observers\ModelObserver;

class NominationObserver extends ModelObserver
{
    public function created(Nomination $nomination)
    {
        Logger::log('award.nominate', true, $this->getCreatedAttributes($nomination));
    }

    public function updated(Nomination $nomination)
    {
        $attributes = $this->getUpdatedAttributes($nomination);

        if (isset($attributes['approved'])) {
            Logger::log('award-nomination.' . ($attributes['approved'] ? 'approve' : 'unapprove'), true, ['id' => $nomination->id]);
        }
    }

    public function deleted(Nomination $nomination)
    {
        Logger::log('award-nomination.delete', true, $this->getDeletedAttributes($nomination));
    }
}