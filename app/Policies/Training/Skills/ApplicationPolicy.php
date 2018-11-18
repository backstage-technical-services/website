<?php

namespace App\Policies\Training\Skills;

use App\Models\Training\Skills\Application;
use App\Models\Users\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ApplicationPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can apply for a skill level.
     *
     * @param \App\Models\Users\User $user
     *
     * @return bool
     */
    public function apply(User $user)
    {
        return $user->isMember();
    }

    /**
     * Determine whether the user can view the details of an application.
     *
     * @param \App\Models\Users\User                  $user
     * @param \App\Models\Training\Skills\Application $application
     *
     * @return bool
     */
    public function view(User $user, Application $application)
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can update the details of a application.
     *
     * @param \App\Models\Users\User                  $user
     * @param \App\Models\Training\Skills\Application $application
     *
     * @return bool
     */
    public function update(User $user, Application $application)
    {
        return $user->isAdmin();
    }
}
