<?php

namespace App\Observers\Elections;

use App\Logger;
use App\Models\Elections\Election;
use App\Observers\ModelObserver;

class ElectionObserver extends ModelObserver
{
    public function created(Election $election)
    {
        Logger::log('election.create', true, $this->getCreatedAttributes($election));
    }

    public function updated(Election $election)
    {
        Logger::log('election.edit', true, $this->getUpdatedAttributes($election));
    }

    public function deleted(Election $election)
    {
        Logger::log('election.delete', true, $this->getDeletedAttributes($election));
    }
}
