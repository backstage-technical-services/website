<?php

namespace Database\Factories\Elections;

use App\Models\Elections\Nomination;
use Database\Factories\Resources\IssueFactory;
use Database\Factories\Users\UserFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\File;

/**
 * A candidate in an election. position is the key of the position in the election's positions. The manifesto is
 * stored as resources/elections/{electionId}/{nominationId}.pdf; use withManifesto() to write one.
 *
 * @extends Factory<Nomination>
 */
class NominationFactory extends Factory
{
    protected $model = Nomination::class;

    public function definition(): array
    {
        return [
            'election_id' => ElectionFactory::new(),
            'user_id' => UserFactory::new(),
            'position' => 0,
            'elected' => false,
        ];
    }

    public function position(int $position): static
    {
        return $this->state(['position' => $position]);
    }

    public function elected(): static
    {
        return $this->state(['elected' => true]);
    }

    public function withManifesto(string $contents = IssueFactory::PDF): static
    {
        return $this->afterCreating(function (Nomination $nomination) use ($contents) {
            File::ensureDirectoryExists(dirname($nomination->getManifestoPath()));
            File::put($nomination->getManifestoPath(), $contents);
        });
    }
}
