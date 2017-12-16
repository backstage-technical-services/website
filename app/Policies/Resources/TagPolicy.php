<?php

namespace App\Policies\Resources;

use App\Models\Resources\Tag;
use App\Models\Users\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class TagPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user view the list of resource tags.
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
     * Determine whether the user can create resource tags.
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
     * Determine whether the user can update a resource tag.
     *
     * @param \App\Models\Users\User    $user
     * @param \App\Models\Resources\Tag $tag
     *
     * @return bool
     */
    public function update(User $user, Tag $tag)
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can delete a resource category.
     *
     * @param \App\Models\Users\User    $user
     * @param \App\Models\Resources\Tag $tag
     *
     * @return bool
     */
    public function delete(User $user, Tag $tag)
    {
        return $user->isAdmin();
    }
}
