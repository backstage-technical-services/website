<?php

namespace App\Observers\Training\Skills;

use App\Logger;
use App\Models\Training\Skills\Application;
use App\Observers\ModelObserver;

class ApplicationObserver extends ModelObserver
{
    public function created(Application $application)
    {
        Logger::log('training-skill.apply', true, $this->getCreatedAttributes($application));
    }
}
