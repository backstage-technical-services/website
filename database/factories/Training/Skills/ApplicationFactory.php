<?php

namespace Database\Factories\Training\Skills;

use App\Models\Training\Skills\Application;
use App\Models\Users\User;
use Database\Factories\Users\UserFactory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * An application for a skill level. There's no status column: awarded_date NULL means it's pending, and an
 * awarded_level of 0 means it was declined.
 *
 * @extends Factory<Application>
 */
class ApplicationFactory extends Factory
{
    protected $model = Application::class;

    public function definition(): array
    {
        return [
            'skill_id' => SkillFactory::new(),
            'user_id' => UserFactory::new(),
            'applied_level' => 1,
            'reasoning' => fake()->paragraph(),
            'date' => now(),
            'awarded_level' => null,
            'awarded_by' => null,
            'awarded_comment' => null,
            'awarded_date' => null,
        ];
    }

    public function forLevel(int $level): static
    {
        return $this->state(['applied_level' => $level]);
    }

    public function awarded(User $by, ?int $level = null, string $comment = ''): static
    {
        return $this->state(
            fn(array $attributes) => [
                'awarded_level' => $level ?? $attributes['applied_level'],
                'awarded_by' => $by->id,
                'awarded_comment' => $comment,
                'awarded_date' => now(),
            ],
        );
    }

    public function declined(User $by, string $comment = 'Not yet'): static
    {
        return $this->awarded($by, 0, $comment);
    }
}
