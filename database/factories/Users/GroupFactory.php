<?php

namespace Database\Factories\Users;

use App\Models\Users\Group;
use App\Models\Users\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * The user_groups table holds the five fixed groups. The tests seed them (see Tests\Support\UserGroupSeeder), so use
 * GroupFactory::findOrCreate() to get one rather than creating duplicates.
 *
 * @extends Factory<Group>
 */
class GroupFactory extends Factory
{
    protected $model = Group::class;

    /**
     * Get the group with the given name, creating it if it doesn't exist.
     */
    public static function findOrCreate(string $name): Group
    {
        return Group::where('name', $name)->first() ?? static::new()->named($name)->createOne();
    }

    public function definition(): array
    {
        return ['title' => 'Standard Member', 'name' => 'member'];
    }

    public function named(string $name): static
    {
        return $this->state(['title' => User::$AccountTypes[$name], 'name' => $name]);
    }

    /**
     * The Group model has timestamps turned on, but the table has no timestamp columns.
     *
     * @param array<string, mixed> $attributes
     */
    public function newModel(array $attributes = []): Group
    {
        $group = parent::newModel($attributes);
        $group->timestamps = false;

        return $group;
    }
}
