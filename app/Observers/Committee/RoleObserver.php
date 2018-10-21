<?php

namespace App\Observers\Committee;

use App\Logger;
use App\Models\Committee\Role;
use App\Observers\ModelObserver;

class RoleObserver extends ModelObserver
{
    public function created(Role $role)
    {
        Logger::log('committee-role.create', true, $this->getCreatedAttributes($role));
    }

    public function updated(Role $role)
    {
        Logger::log('committee-role.edit', true, $this->getUpdatedAttributes($role));
    }

    public function deleted(Role $role)
    {
        Logger::log('committee-role.delete', true, $this->getDeletedAttributes($role));
    }
}