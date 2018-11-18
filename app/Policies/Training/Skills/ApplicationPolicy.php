<?php

namespace App\Policies\Training\Skills;

use App\Models\Training\Skills\Application;
use App\Models\Users\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ApplicationPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can propose a skill level.
     *
     * @param \App\Models\Users\User $user
     *
     * @return bool
     */
    public function propose(User $user)
    {
        return $user->isMember();
    }

    /**
     * Determine whether the user can view the details of a proposal.
     *
     * @param \App\Models\Users\User                  $user
     * @param \App\Models\Training\Skills\Application $proposal
     *
     * @return bool
     */
    public function view(User $user, Application $proposal)
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can update the details of a proposal.
     *
     * @param \App\Models\Users\User                  $user
     * @param \App\Models\Training\Skills\Application $proposal
     *
     * @return bool
     */
    public function update(User $user, Application $proposal)
    {
        return $user->isAdmin();
    }
}
