<?php

namespace Database\Factories\Committee;

use App\Models\Committee\Role;
use App\Models\Users\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * A committee role. The Role model's mutators have side effects: setting user_id moves people in and out of the
 * committee group (and needs someone logged in), and setting order shifts the other roles. This factory sets the
 * attributes directly, without those side effects. By default the role goes at the end and has no holder.
 *
 * @extends Factory<Role>
 */
class RoleFactory extends Factory
{
    protected $model = Role::class;

    public function definition(): array
    {
        return [
            'name' => fake()->unique()->jobTitle(),
            'description' => fake()->sentence(),
            'email' => fake()->unique()->userName() . '@bts-crew.com',
            'user_id' => null,
            'order' => fn() => ((int) Role::max('order')) + 1,
        ];
    }

    public function heldBy(User $user): static
    {
        return $this->state(['user_id' => $user->id]);
    }

    /**
     * @param array<string, mixed> $attributes
     */
    public function newModel(array $attributes = []): Role
    {
        $role = new Role();
        $role->setRawAttributes($attributes);

        return $role;
    }
}
