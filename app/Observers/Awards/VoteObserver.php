<?php

namespace App\Observers\Awards;

use App\Logger;
use App\Models\Awards\Vote;
use App\Observers\ModelObserver;

class VoteObserver extends ModelObserver
{
    public function created(Vote $vote)
    {
        Logger::log('award.vote', true, $this->getCreatedAttributes($vote));
    }
}