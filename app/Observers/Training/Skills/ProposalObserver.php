<?php

namespace App\Observers\Training\Skills;

use App\Logger;
use App\Models\Training\Skills\Proposal;
use App\Observers\ModelObserver;

class ProposalObserver extends ModelObserver
{
    public function created(Proposal $proposal)
    {
        Logger::log('training-skill.propose', true, $this->getCreatedAttributes($proposal));
    }
}