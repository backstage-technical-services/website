<?php

namespace App\Policies\Events;

use App\Models\Events\Time;
use App\Models\Users\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class EventTimePolicy
{
    use HandlesAuthorization;

    /**
     * Determine if the user can update a crew entry.
     *
     * @param \App\Models\Users\User  $user
     * @param \App\Models\Events\Time $time
     *
     * @return bool
     */
    public function update(User $user, Time $time)
    {
        return $user->can('update', $time->event);
    }

    /**
     * Determine if the user can delete a crew entry.
     *
     * @param \App\Models\Users\User  $user
     * @param \App\Models\Events\Time $time
     *
     * @return bool
     */
    public function delete(User $user, Time $time)
    {
        return $user->can('update', $time->event);
    }
}
