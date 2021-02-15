<?php

namespace Database\Factories\Users;

use App\Models\Users\Group;
use Illuminate\Database\Eloquent\Factories\Factory;

class GroupFactory extends Factory
{
    protected string $model = Group::class;
    
    public function definition(): array
    {
        return [
            'title' => 'Member',
            'name'  => 'member'
        ];
    }
}
