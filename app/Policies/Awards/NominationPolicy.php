<?php

namespace App\Policies\Awards;

use App\Models\Awards\Nomination;
use App\Models\Users\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class NominationPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can update nominations.
     *
     * @param \App\Models\Users\User        $user
     * @param \App\Models\Awards\Nomination $awardNomination
     *
     * @return bool
     */
    public function edit(User $user, Nomination $awardNomination)
    {
        return $user->isAdmin() && $awardNomination->season->areNominationsOpen();
    }
}
