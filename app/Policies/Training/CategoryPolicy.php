<?php

namespace App\Policies\Training;

use App\Models\Training\Category;
use App\Models\Users\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CategoryPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the list of training categories.
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
     * Determine whether the user can create a new training category.
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
     * Determine whether the user can update the details of a training category.
     *
     * @param \App\Models\Users\User        $user
     * @param \App\Models\Training\Category $category
     *
     * @return bool
     */
    public function update(User $user, Category $category)
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can delete a training category.
     *
     * @param \App\Models\Users\User        $user
     * @param \App\Models\Training\Category $category
     *
     * @return bool
     */
    public function delete(User $user, Category $category)
    {
        return $user->isAdmin();
    }
}
