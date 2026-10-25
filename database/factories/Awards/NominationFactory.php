<?php

namespace Database\Factories\Awards;

use App\Models\Awards\Nomination;
use Database\Factories\Users\UserFactory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * A nomination for an award in a season. The nominee is free text. won has no default, and the site doesn't set it
 * when someone nominates, so MariaDB stores 0. This stores false explicitly so that the rows look the same on any
 * database.
 *
 * @extends Factory<Nomination>
 */
class NominationFactory extends Factory
{
    protected $model = Nomination::class;

    public function definition(): array
    {
        return [
            'award_id' => AwardFactory::new(),
            'award_season_id' => SeasonFactory::new(),
            'nominee' => fake()->name(),
            'reason' => fake()->sentence(),
            'approved' => false,
            'won' => false,
            'suggested_by' => UserFactory::new(),
        ];
    }

    public function approved(): static
    {
        return $this->state(['approved' => true]);
    }

    public function won(): static
    {
        return $this->state(['won' => true]);
    }
}
