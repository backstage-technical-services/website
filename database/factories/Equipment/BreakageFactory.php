<?php

namespace Database\Factories\Equipment;

use App\Models\Equipment\Breakage;
use Database\Factories\Users\UserFactory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * A breakage report. comment is NOT NULL with no default, and the site doesn't set it when a breakage is reported, so
 * MariaDB stores ''. This stores '' explicitly so that the rows look the same on any database.
 *
 * @extends Factory<Breakage>
 */
class BreakageFactory extends Factory
{
    protected $model = Breakage::class;

    public function definition(): array
    {
        return [
            'name' => fake()->randomElement(['Par can', 'XLR cable', 'Moving head', 'DI box']),
            'location' => 'Store',
            'label' => fake()->bothify('BTS-####'),
            'description' => fake()->sentence(),
            'comment' => '',
            'status' => Breakage::STATUS_REPORTED,
            'closed' => false,
            'user_id' => UserFactory::new(),
        ];
    }

    public function status(int $status): static
    {
        return $this->state(['status' => $status]);
    }

    public function resolved(): static
    {
        return $this->status(Breakage::STATUS_RESOLVED);
    }

    public function closed(): static
    {
        return $this->state(['closed' => true]);
    }
}
