<?php

namespace Database\Factories\Awards;

use App\Models\Awards\Nomination;
use App\Models\Awards\Vote;
use Database\Factories\Users\UserFactory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * A vote for a nomination. Use forNomination() so that the vote's season matches the nomination's.
 *
 * @extends Factory<Vote>
 */
class VoteFactory extends Factory
{
    protected $model = Vote::class;

    public function definition(): array
    {
        return [
            'award_season_id' => SeasonFactory::new(),
            'nomination_id' => NominationFactory::new(),
            'user_id' => UserFactory::new(),
        ];
    }

    public function forNomination(Nomination $nomination): static
    {
        return $this->state([
            'award_season_id' => $nomination->award_season_id,
            'nomination_id' => $nomination->id,
        ]);
    }
}
