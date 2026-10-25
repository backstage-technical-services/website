<?php

namespace Database\Factories\Users;

use App\Models\Users\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * Creates users directly in the database. Unlike User::create(), this doesn't create them in Keycloak or email them,
 * but it does give them the same diary preferences. Their usernames look like Bath usernames (eg, abc123), as the users
 * routes only accept letters, numbers and underscores.
 *
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected $model = User::class;

    public function definition(): array
    {
        $username = fake()->unique()->regexify('[a-z]{2,3}[0-9]{2,4}');

        return [
            'username' => $username,
            'email' => "{$username}@bath.ac.uk",
            'password' => '',
            'forename' => fake()->firstName(),
            'surname' => fake()->lastName(),
            'nickname' => null,
            'phone' => null,
            'address' => null,
            'tool_colours' => null,
            'dob' => null,
            'show_email' => true,
            'show_phone' => false,
            'show_address' => false,
            'show_age' => false,
            'diary_preferences' => [
                'event_types' => ['event', 'training', 'social', 'meeting', 'hidden'],
                'crewing' => '*',
            ],
            'export_token' => null,
            'user_group_id' => fn() => GroupFactory::findOrCreate('member')->id,
            'status' => true,
            'keycloak_user_id' => null,
        ];
    }

    /**
     * Put the user in a group: member, associate, committee, staff or super_admin.
     */
    public function inGroup(string $group): static
    {
        return $this->state(['user_group_id' => fn() => GroupFactory::findOrCreate($group)->id]);
    }

    public function member(): static
    {
        return $this->inGroup('member');
    }

    public function associate(): static
    {
        return $this->inGroup('associate');
    }

    public function committee(): static
    {
        return $this->inGroup('committee');
    }

    public function staff(): static
    {
        return $this->inGroup('staff');
    }

    public function superAdmin(): static
    {
        return $this->inGroup('super_admin');
    }

    /**
     * Archive the account (status = 0). Archived users keep their group.
     */
    public function archived(): static
    {
        return $this->state(['status' => false]);
    }
}
