<?php

namespace App\Policies\Resources;

use App\Models\Resources\Resource;
use App\Models\Users\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ResourcePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user view the list of resources.
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
     * Determine whether the user can create resources.
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
     * Determine whether the user can update a resource.
     *
     * @param \App\Models\Users\User $user
     * @param Resource  $resource
     *
     * @return bool
     */
    public function update(User $user, Resource $resource)
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can delete a resource.
     *
     * @param \App\Models\Users\User $user
     * @param Resource  $resource
     *
     * @return bool
     */
    public function delete(User $user, Resource $resource)
    {
        return $user->isAdmin();
    }
}
