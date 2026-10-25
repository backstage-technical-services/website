<?php

namespace Database\Factories\Training\Skills;

use App\Models\Training\Skills\Skill;
use Database\Factories\Training\CategoryFactory;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * A skill offers a level if that level's requirements aren't NULL. By default this offers all three and has no
 * category ("Uncategorised").
 *
 * @extends Factory<Skill>
 */
class SkillFactory extends Factory
{
    protected $model = Skill::class;

    public function definition(): array
    {
        return [
            'name' => Str::title(fake()->unique()->word() . ' ' . fake()->word()),
            'category_id' => null,
            'description' => fake()->sentence(),
            'level1' => 'Can do it under supervision',
            'level2' => 'Can do it unsupervised',
            'level3' => 'Can teach it',
        ];
    }

    public function inCategory(?CategoryFactory $category = null): static
    {
        return $this->state(['category_id' => $category ?? CategoryFactory::new()]);
    }

    /**
     * Only offer the given levels.
     */
    public function levels(int ...$levels): static
    {
        return $this->state(
            fn(array $attributes) => [
                'level1' => in_array(1, $levels) ? $attributes['level1'] : null,
                'level2' => in_array(2, $levels) ? $attributes['level2'] : null,
                'level3' => in_array(3, $levels) ? $attributes['level3'] : null,
            ],
        );
    }
}
