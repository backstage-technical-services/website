<?php

namespace Database\Factories\Awards;

use App\Models\Awards\Award;
use App\Models\Users\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * An award in the catalogue. An award with suggested_by set is a member's suggestion that hasn't been approved yet.
 *
 * @extends Factory<Award>
 */
class AwardFactory extends Factory
{
    protected $model = Award::class;

    public function definition(): array
    {
        return [
            'name' => 'Best ' . fake()->unique()->word(),
            'description' => fake()->sentence(),
            'suggested_by' => null,
            'recurring' => true,
        ];
    }

    public function suggestedBy(User $user): static
    {
        return $this->state(['suggested_by' => $user->id]);
    }
}
