<?php

namespace App\Policies\Events;

use App\Models\Events\Event;
use App\Models\Users\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class PaperworkPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user view the list of paperwork.
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
     * Determine whether the user can create paperwork.
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
     * Determine whether the user can update paperwork.
     *
     * @param \App\Models\Users\User   $user
     *
     * @return bool
     */
    public function update(User $user)
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can delete paperwork.
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
