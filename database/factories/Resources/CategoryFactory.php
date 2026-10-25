<?php

namespace Database\Factories\Resources;

use App\Models\Resources\Category;
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
        $name = Str::title(fake()->unique()->word() . ' ' . fake()->word());

        return ['name' => $name, 'slug' => Str::slug($name), 'flag' => null];
    }

    /**
     * Give the category a "flag", such as Category::FLAG_RISK_ASSESSMENT.
     */
    public function flag(int $flag): static
    {
        return $this->state(['flag' => $flag]);
    }
}
