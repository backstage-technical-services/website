<?php

namespace App\Observers\Training\Skills;

use App\Logger;
use App\Models\Training\Skills\Application;
use App\Observers\ModelObserver;

class ApplicationObserver extends ModelObserver
{
    public function created(Application $proposal)
    {
        Logger::log('training-skill.propose', true, $this->getCreatedAttributes($proposal));
    }
}