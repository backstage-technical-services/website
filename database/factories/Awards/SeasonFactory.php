<?php

namespace Database\Factories\Awards;

use App\Models\Awards\Season;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * An award season. A NULL status means it's closed. Awards are attached with hasAttached(AwardFactory::new(), [],
 * 'awards'), as award_award_season is a join table.
 *
 * @extends Factory<Season>
 */
class SeasonFactory extends Factory
{
    protected $model = Season::class;

    public function definition(): array
    {
        return ['name' => 'Awards ' . fake()->unique()->year(), 'status' => null];
    }

    public function status(?int $status): static
    {
        return $this->state(['status' => $status]);
    }

    public function nominationsOpen(): static
    {
        return $this->status(Season::STATUS_NOMINATIONS);
    }

    public function votingOpen(): static
    {
        return $this->status(Season::STATUS_VOTING);
    }

    public function resultsReleased(): static
    {
        return $this->status(Season::STATUS_RESULTS);
    }
}
