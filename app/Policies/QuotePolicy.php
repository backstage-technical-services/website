<?php

namespace App\Policies;

use App\Models\Quote;
use App\Models\Users\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class QuotePolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the quote.
     *
     * @param  \App\Models\Users\User $user
     *
     * @return mixed
     */
    public function view(User $user)
    {
        return $user->isMember();
    }

    /**
     * Determine whether the user can create quotes.
     *
     * @param  \App\Models\Users\User $user
     *
     * @return mixed
     */
    public function create(User $user)
    {
        return $user->isMember();
    }

    /**
     * Determine whether the user can delete the quote.
     *
     * @param  \App\Models\Users\User $user
     *
     * @return mixed
     */
    public function delete(User $user)
    {
        return $user->isAdmin();
    }
}
