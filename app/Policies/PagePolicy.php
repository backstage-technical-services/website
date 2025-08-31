<?php

namespace App\Policies;

use App\Models\Page;
use App\Models\Users\User;
use Illuminate\Auth\Access\HandlesAuthorization;
use Package\Notifications\Facades\Notify;

class PagePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the page index.
     *
     * @param  \App\Models\Users\User $user
     *
     * @return mixed
     */
    public function index(User $user)
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can view the page.
     *
     * @param  \App\Models\Users\User $user
     * @param  \App\Models\Page       $page
     *
     * @return mixed
     */
    public function view(User $user, Page $page)
    {
        if ($page->published == 1) {
            return true;
        } elseif ($user->isAdmin()) {
            Notify::warning(
                'This page will not be viewable by non-admins until it is published.',
                'Page not published',
            );
            return true;
        }

        return false;
    }

    /**
     * Determine whether the user can create pages.
     * @param  \App\Models\Users\User $user
     * @return mixed
     */
    public function create(User $user)
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can update the page.
     * @param  \App\Models\Users\User $user
     * @param  \App\Models\Page       $page
     * @return mixed
     */
    public function update(User $user, Page $page)
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can delete the page.
     * @param  \App\Models\Users\User $user
     * @param  \App\Models\Page       $page
     * @return mixed
     */
    public function delete(User $user, Page $page)
    {
        return $user->isAdmin();
    }
}
