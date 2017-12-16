<?php

namespace App\Policies\Resources;

use App\Models\Resources\Category;
use App\Models\Users\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CategoryPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user view the list of resource categories.
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
     * Determine whether the user can create resource categories.
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
     * Determine whether the user can update a resource category.
     *
     * @param \App\Models\Users\User         $user
     * @param \App\Models\Resources\Category $category
     *
     * @return bool
     */
    public function update(User $user, Category $category)
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can delete a resource category.
     *
     * @param \App\Models\Users\User         $user
     * @param \App\Models\Resources\Category $category
     *
     * @return bool
     */
    public function delete(User $user, Category $category)
    {
        return $user->isAdmin();
    }
}
