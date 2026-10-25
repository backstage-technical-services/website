<?php

namespace Database\Factories\Events;

use App\Models\Events\Crew;
use Database\Factories\Users\UserFactory;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * A crew row is "core" crew if it has a role name, "general" crew if it doesn't, and a guest (socials only) if it has
 * no user. By default this creates general crew.
 *
 * guest_name is NOT NULL with no default. The site never sets it for members, so MariaDB stores '' (it isn't in
 * strict mode). This stores '' explicitly so that the rows look the same on any database.
 *
 * @extends Factory<Crew>
 */
class CrewFactory extends Factory
{
    protected $model = Crew::class;

    public function definition(): array
    {
        return [
            'event_id' => EventFactory::new(),
            'user_id' => UserFactory::new(),
            'name' => null,
            'em' => false,
            'confirmed' => false,
            'guest_name' => '',
        ];
    }

    /**
     * Core crew, with a named role.
     */
    public function role(string $name = 'Lighting Designer'): static
    {
        return $this->state(['name' => $name]);
    }

    /**
     * An EM role, which gives the same rights over the event as the TEM.
     */
    public function eventManager(string $name = 'Event Manager'): static
    {
        return $this->state(['name' => $name, 'em' => true]);
    }

    /**
     * A guest on a social, who isn't a user.
     */
    public function guest(string $name = 'A Guest'): static
    {
        return $this->state(['user_id' => null, 'name' => null, 'guest_name' => $name]);
    }

    /**
     * Attended (for training) or paid (for socials).
     */
    public function confirmed(): static
    {
        return $this->state(['confirmed' => true]);
    }
}
