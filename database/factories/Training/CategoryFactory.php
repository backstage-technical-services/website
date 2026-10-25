<?php

namespace Database\Factories\Training;

use App\Models\Training\Category;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Category>
 */
class CategoryFactory extends Factory
{
    protected $model = Category::class;

    public function definition(): array
    {
        return ['name' => Str::title(fake()->unique()->word() . ' ' . fake()->word())];
    }
}
