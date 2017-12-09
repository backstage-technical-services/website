<?php

namespace App\Policies\Awards;

use App\Models\Awards\Award;
use App\Models\Users\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class AwardPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the list of awards.
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
     * Determine whether the user can create awards.
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
     * Determine whether the user can update awards.
     *
     * @param \App\Models\Users\User   $user
     * @param \App\Models\Awards\Award $award
     *
     * @return bool
     */
    public function update(User $user, Award $award)
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can suggest new awards.
     *
     * @param \App\Models\Users\User $user
     *
     * @return bool
     */
    public function suggest(User $user)
    {
        return $user->isMember();
    }

    /**
     * Determine whether the user can delete awards.
     *
     * @param \App\Models\Users\User                $user
     * @param \App\Models\Awards\Award $award
     *
     * @return bool
     */
    public function delete(User $user, Award $award)
    {
        return $user->isAdmin();
    }
}
