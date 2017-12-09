<?php

namespace App\Policies\Awards;

use App\Models\Awards\Season;
use App\Models\Users\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SeasonPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the list of award seasons.
     *
     * @param \App\Models\Users\User $user
     *
     * @return bool
     */
    public function index(User $user)
    {
        return $user->isMember();
    }

    /**
     * Determine whether the user can create award seasons.
     *
     * @param \App\Models\Users\User $user
     *
     * @return boolø
     */
    public function create(User $user)
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can view award seasons.
     *
     * @param \App\Models\Users\User    $user
     * @param \App\Models\Awards\Season $season
     *
     * @return bool
     */
    public function view(User $user, Season $season)
    {
        return $user->isMember();
    }

    /**
     * Determine whether the user can update award seasons.
     *
     * @param \App\Models\Users\User    $user
     * @param \App\Models\Awards\Season $season
     *
     * @return bool
     */
    public function update(User $user, Season $season)
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can nominate.
     *
     * @param \App\Models\Users\User    $user
     * @param \App\Models\Awards\Season $season
     *
     * @return bool
     */
    public function nominate(User $user, Season $season)
    {
        return $user->isMember() && $season->areNominationsOpen();
    }

    /**
     * Determine whether the user can vote.
     *
     * @param \App\Models\Users\User    $user
     * @param \App\Models\Awards\Season $season
     *
     * @return bool
     */
    public function vote(User $user, Season $season)
    {
        return $user->isMember() && $season->isVotingOpen();
    }

    /**
     * Determine whether the user can delete award seasons.
     *
     * @param \App\Models\Users\User    $user
     * @param \App\Models\Awards\Season $season
     *
     * @return bool
     */
    public function delete(User $user, Season $season)
    {
        return $user->isAdmin();
    }
}
