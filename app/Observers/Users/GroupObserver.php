<?php

namespace App\Observers\Users;

use App\Logger;
use App\Models\Users\Group;
use App\Observers\ModelObserver;

class GroupObserver extends ModelObserver
{
    public function created(Group $group)
    {
        Logger::log('user-group.create', true, $this->getCreatedAttributes($group));
    }

    public function updated(Group $group)
    {
        Logger::log('user-group.edit', true, $this->getUpdatedAttributes($group));
    }

    public function deleted(Group $group)
    {
        Logger::log('user-group.delete', true, $this->getDeletedAttributes($group));
    }
}