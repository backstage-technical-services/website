<?php

namespace Database\Factories\Elections;

use App\Models\Elections\Election;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * An election. Positions are stored as JSON in two shapes: full elections (type 1) key them by committee role ID, and
 * by-elections (type 2) are a list. By default this creates a by-election that's open for nominations.
 *
 * The dates are shifted by the browser's timezone offset when they're saved (see AccountsForTimezones). The tests use
 * an offset of 0, so the stored dates are the ones given here.
 *
 * @extends Factory<Election>
 */
class ElectionFactory extends Factory
{
    protected $model = Election::class;

    public function definition(): array
    {
        return [
            'type' => 2,
            'positions' => ['Chair', 'Secretary'],
            'nominations_start' => now()->subDay(),
            'nominations_end' => now()->addDays(6),
            'voting_start' => now()->addDays(7),
            'voting_end' => now()->addDays(9),
            'hustings_time' => now()->addDays(6)->setTime(18, 0),
            'hustings_location' => 'The Plug',
            'bathstudent_id' => null,
        ];
    }

    /**
     * A full election, with the positions keyed by committee role ID.
     *
     * @param array<int, string> $positions
     */
    public function full(array $positions = [1 => 'Chair', 2 => 'Secretary']): static
    {
        return $this->state(['type' => 1, 'positions' => $positions]);
    }

    public function nominationsOpen(): static
    {
        return $this->state([
            'nominations_start' => now()->subDay(),
            'nominations_end' => now()->addDays(6),
            'voting_start' => now()->addDays(7),
            'voting_end' => now()->addDays(9),
        ]);
    }

    public function votingOpen(): static
    {
        return $this->state([
            'nominations_start' => now()->subDays(9),
            'nominations_end' => now()->subDays(2),
            'voting_start' => now()->subDay(),
            'voting_end' => now()->addDay(),
        ]);
    }

    public function votingClosed(): static
    {
        return $this->state([
            'nominations_start' => now()->subWeeks(3),
            'nominations_end' => now()->subWeeks(2),
            'voting_start' => now()->subWeek(),
            'voting_end' => now()->subDays(2),
        ]);
    }
}
