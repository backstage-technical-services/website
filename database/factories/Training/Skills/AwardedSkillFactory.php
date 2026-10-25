<?php

namespace Database\Factories\Training\Skills;

use App\Models\Training\Skills\AwardedSkill;
use Database\Factories\Users\UserFactory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * A member's level in a skill, awarded by another user. The code assumes one row per user and skill, but nothing
 * enforces it.
 *
 * @extends Factory<AwardedSkill>
 */
class AwardedSkillFactory extends Factory
{
    protected $model = AwardedSkill::class;

    public function definition(): array
    {
        return [
            'skill_id' => SkillFactory::new(),
            'user_id' => UserFactory::new(),
            'level' => 1,
            'awarded_by' => UserFactory::new(),
        ];
    }

    public function level(int $level): static
    {
        return $this->state(['level' => $level]);
    }
}
