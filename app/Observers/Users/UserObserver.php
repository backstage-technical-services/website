<?php

namespace App\Observers\Users;

use App\Logger;
use App\Models\Users\User;
use App\Observers\ModelObserver;

class UserObserver extends ModelObserver
{
    public function created(User $user)
    {
        Logger::log('user.create', true, $user->getAttributes());
    }

    public function updated(User $user)
    {
        $attributes = $this->getUpdatedAttributes($user);

        if (isset($attributes['status'])) {
            Logger::log('user.' . ($attributes['status'] ? 'unarchive' : 'archive'), true, ['id' => $user->id]);
        } else if (count($cleaned = $this->cleanForSaving($user, $attributes)) > 1) {
            Logger::log('user.edit', true, $cleaned);
        } else if (isset($attributes['password'])) {
            Logger::log('user.edit-password', true, ['id' => $user->id]);
        }
    }

    public function deleted(User $user)
    {
        Logger::log('user.delete', true, $user->getAttributes());
    }
}