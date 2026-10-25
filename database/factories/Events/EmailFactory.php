<?php

namespace Database\Factories\Events;

use App\Models\Events\Email;
use Database\Factories\Users\UserFactory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * An email sent to an event's crew. Creating one doesn't send anything.
 *
 * @extends Factory<Email>
 */
class EmailFactory extends Factory
{
    protected $model = Email::class;

    public function definition(): array
    {
        return [
            'event_id' => EventFactory::new(),
            'sender_id' => UserFactory::new(),
            'header' => fake()->sentence(),
            'body' => fake()->paragraph(),
        ];
    }
}
