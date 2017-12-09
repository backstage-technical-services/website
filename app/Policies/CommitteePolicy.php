<?php

namespace App\Policies;

use App\Models\Committee\Role;
use App\Models\Users\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CommitteePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can create committeeRoles.
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
     * Determine whether the user can update the committeeRole.
     *
     * @param  \App\Models\Users\User     $user
     * @param  \App\Models\Committee\Role $committeeRole
     *
     * @return mixed
     */
    public function update(User $user, Role $committeeRole)
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can delete the committeeRole.
     *
     * @param  \App\Models\Users\User     $user
     * @param  \App\Models\Committee\Role $committeeRole
     *
     * @return mixed
     */
    public function delete(User $user, Role $committeeRole)
    {
        return $user->isAdmin();
    }
}
