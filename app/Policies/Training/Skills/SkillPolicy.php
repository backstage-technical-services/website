<?php

namespace App\Policies\Training\Skills;

use App\Models\Training\Skills\Skill;
use App\Models\Users\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class SkillPolicy
{
    use HandlesAuthorization;

    /**
     * Determine whether the user can view the training skills index.
     *
     * @param \App\Models\Users\User $user
     *
     * @return bool
     */
    public function index(User $user)
    {
        return $user->isMember() || $user->isStaff();
    }

    /**
     * Determine whether the user can create a new training skill.
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
     * Determine whether the user can view a training skill.
     *
     * @param \App\Models\Users\User            $user
     * @param \App\Models\Training\Skills\Skill $skill
     *
     * @return bool
     */
    public function view(User $user, Skill $skill)
    {
        return $user->isMember() || $user->isStaff();
    }

    /**
     * Determine whether the user can update a training skill.
     *
     * @param \App\Models\Users\User            $user
     * @param \App\Models\Training\Skills\Skill $skill
     *
     * @return bool
     */
    public function edit(User $user, Skill $skill)
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can delete a training skill.
     *
     * @param \App\Models\Users\User            $user
     * @param \App\Models\Training\Skills\Skill $skill
     *
     * @return bool
     */
    public function delete(User $user, Skill $skill)
    {
        return $user->isAdmin();
    }

    /**
     * Determine whether the user can propose a skill level.
     *
     * @param \App\Models\Users\User $user
     *
     * @return bool
     */
    public function propose(User $user)
    {
        return $user->isMember();
    }

    /**
     * Determine whether the user can award a skill level to other members.
     *
     * @param \App\Models\Users\User            $user
     * @param \App\Models\Training\Skills\Skill $skill
     *
     * @return bool
     */
    public function award(User $user, Skill $skill = null)
    {
        return $user->canAwardSkill($skill);
    }

    /**
     * Determine whether the user can revoke a skill level from other members.
     *
     * @param \App\Models\Users\User            $user
     * @param \App\Models\Training\Skills\Skill $skill
     *
     * @return bool
     */
    public function revoke(User $user, Skill $skill = null)
    {
        return $user->canAwardSkill($skill);
    }

    /**
     * Determine whether the user can view the skills log.
     *
     * @param \App\Models\Users\User $user
     *
     * @return bool
     */
    public function log(User $user)
    {
        return $user->isAdmin();
    }
}
