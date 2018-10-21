<?php

namespace App\Observers\Awards;

use App\Logger;
use App\Models\Awards\Award;
use App\Observers\ModelObserver;

class AwardObserver extends ModelObserver
{
    public function created(Award $award)
    {
        $attributes = $this->getCreatedAttributes($award);

        if (is_null($attributes['suggested_by'])) {
            Logger::log('award.create', true, $attributes);
        } else {
            Logger::log('award.suggest', true, $attributes);
        }
    }

    public function updated(Award $award)
    {
        $attributes = $this->getUpdatedAttributes($award);

        if (is_null($attributes['suggested_by'])) {
            Logger::log('award.approve', true, ['id' => $award->id]);
        } else {
            Logger::log('award.edit', true, $attributes);
        }
    }

    public function deleted(Award $award)
    {
        Logger::log('award.delete', true, $this->getDeletedAttributes($award));
    }
}