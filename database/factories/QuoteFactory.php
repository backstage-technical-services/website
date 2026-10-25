<?php

namespace Database\Factories;

use App\Models\Quote;
use Database\Factories\Users\UserFactory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * A quote on the quotes board. The culprit is free text.
 *
 * @extends Factory<Quote>
 */
class QuoteFactory extends Factory
{
    protected $model = Quote::class;

    public function definition(): array
    {
        return [
            'culprit' => fake()->name(),
            'quote' => fake()->sentence(),
            'date' => now()->subDay(),
            'added_by' => UserFactory::new(),
        ];
    }
}
