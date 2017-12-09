<?php

namespace App\Policies\Events;

use App\Models\Events\Event;
use App\Models\Users\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class EventPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user view the list of events.
     *
     * @param \App\Models\Users\User $user
     *
     * @return bool
     */
    public function index(User $user)
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can create events.
     *
     * @param \App\Models\Users\User $user
     *
     * @return bool
     */
    public function create(User $user)
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can update events.
     *
     * @param \App\Models\Users\User   $user
     * @param \App\Models\Events\Event $event
     *
     * @return bool
     */
    public function update(User $user, Event $event)
    {
        return $user->isAdmin() || $user->hasEMRole($event);
    }

    /**
     * Determine whether the user can volunteer for an event.
     *
     * @param \App\Models\Users\User   $user
     * @param \App\Models\Events\Event $event
     *
     * @return bool
     */
    public function volunteer(User $user, Event $event)
    {
        return $event->isCrewListOpen() && $user->isMember() && !$event->isTEM($user);
    }

    /**
     * Determine whether the user can delete events.
     *
     * @param \App\Models\Users\User $user
     *
     * @return bool
     */
    public function delete(User $user)
    {
        return $user->isAdmin();
    }
}
