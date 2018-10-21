<?php

namespace App\Observers\Equipment;

use App\Logger;
use App\Models\Equipment\Breakage;
use App\Observers\ModelObserver;

class BreakageObserver extends ModelObserver
{
    public function created(Breakage $breakage)
    {
        Logger::log('equipment-breakage.create', true, $this->getCreatedAttributes($breakage));
    }

    public function updated(Breakage $breakage)
    {
        Logger::log('equipment-breakage.edit', true, $this->getUpdatedAttributes($breakage));
    }

    public function deleted(Breakage $breakage)
    {
        Logger::log('equipment-breakage.delete', true, $this->getDeletedAttributes($breakage));
    }
}