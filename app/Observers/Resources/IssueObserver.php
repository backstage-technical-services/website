<?php

namespace App\Observers\Resources;

use App\Logger;
use App\Models\Resources\Issue;
use App\Observers\ModelObserver;

class IssueObserver extends ModelObserver
{
    public function created(Issue $issue)
    {
        if ($issue->issue > 1) {
            Logger::log('resource.issue', true, $this->getCreatedAttributes($issue));
        }
    }
}