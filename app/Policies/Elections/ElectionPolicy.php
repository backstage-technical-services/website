<?php

namespace App\Policies\Elections;

use App\Models\Elections\Election;
use App\Models\Users\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ElectionPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the election list.
     *
     * @param  \App\Models\Users\User $user
     *
     * @return mixed
     */
    public function index(User $user)
    {
        return $user->isMember();
    }

    /**
     * Determine whether the user can view the election.
     *
     * @param  \App\Models\Users\User         $user
     * @param  \App\Models\Elections\Election $election
     *
     * @return mixed
     */
    public function view(User $user, Election $election)
    {
        return $user->isMember();
    }

    /**
     * Determine whether the user can create elections.
     *
     * @param  \App\Models\Users\User $user
     *
     * @return mixed
     */
    public function create(User $user)
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can update the election.
     *
     * @param  \App\Models\Users\User                      $user
     * @param  \App\Models\Elections\Election $election
     *
     * @return mixed
     */
    public function update(User $user, Election $election)
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can delete the election.
     *
     * @param  \App\Models\Users\User                      $user
     * @param  \App\Models\Elections\Election $election
     *
     * @return mixed
     */
    public function delete(User $user, Election $election)
    {
        return $user->isAdmin();
    }
}
