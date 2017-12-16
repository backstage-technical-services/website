<?php

namespace App\Policies\Events;

use App\Models\Events\Crew;
use App\Models\Users\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class EventCrewPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if the user can update a crew entry.
     *
     * @param \App\Models\Users\User  $user
     * @param \App\Models\Events\Crew $crew
     *
     * @return bool
     */
    public function update(User $user, Crew $crew)
    {
        return $user->can('update', $crew->event);
    }

    /**
     * Determine if the user can delete a crew entry.
     *
     * @param \App\Models\Users\User  $user
     * @param \App\Models\Events\Crew $crew
     *
     * @return bool
     */
    public function delete(User $user, Crew $crew)
    {
        return $user->can('update', $crew->event);
    }
}
