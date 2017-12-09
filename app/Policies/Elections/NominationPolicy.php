<?php

    namespace App\Policies\Elections;

    use App\Models\Elections\Nomination;
    use App\Models\Users\User;
    use Illuminate\Auth\Access\HandlesAuthorization;

    class NominationPolicy
    {
        use HandlesAuthorization;

        /**
         * Determine whether the user can create nominations.
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
         * Determine whether the user can view a manifesto.
         *
         * @param \App\Models\Users\User           $user
         * @param \App\Models\Elections\Nomination $electionNomination
         *
         * @return bool
         */
        public function manifesto(User $user, Nomination $electionNomination)
        {
            return $user->isMember();
        }

        /**
         * Determine whether the user can delete nominations.
         *
         * @param  \App\Models\Users\User           $user
         * @param  \App\Models\Elections\Nomination $electionNomination
         *
         * @return mixed
         */
        public function delete(User $user, Nomination $electionNomination)
        {
            return $user->isAdmin();
        }
    }
