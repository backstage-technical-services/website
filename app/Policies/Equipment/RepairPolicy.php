<?php

namespace App\Policies\Equipment;

use App\Models\Equipment\Breakage;
use App\Models\Users\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class RepairPolicy
{
    use HandlesAuthorization;

    /**
     * Determine if the user can view the list of breakages.
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
     * Determine whether the user can view the equipment breakage.
     *
     * @param  \App\Models\Users\User        $user
     * @param \App\Models\Equipment\Breakage $equipmentBreakage
     *
     * @return mixed
     */
    public function view(User $user, Breakage $equipmentBreakage)
    {
        return $user->isMember();
    }

    /**
     * Determine whether the user can create an equipment breakage.
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
     * Determine whether the user can update the equipment breakage.
     *
     * @param  \App\Models\Users\User        $user
     * @param \App\Models\Equipment\Breakage $equipmentBreakage
     *
     * @return mixed
     */
    public function update(User $user, Breakage $equipmentBreakage)
    {
        return $user->isAdmin();
    }
}
