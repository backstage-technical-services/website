<?php

namespace App\Observers\Awards;

use App\Logger;
use App\Models\Awards\Season;
use App\Observers\ModelObserver;

class SeasonObserver extends ModelObserver
{
    public function created(Season $season)
    {
        Logger::log('award-season.create', true, $this->getCreatedAttributes($season));
    }

    public function updated(Season $season)
    {
        Logger::log('award-season.edit', true, $this->getUpdatedAttributes($season));
    }

    public function deleted(Season $season)
    {
        Logger::log('award-season.delete', true, $this->getDeletedAttributes($season));
    }
}
