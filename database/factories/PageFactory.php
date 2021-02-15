<?php

namespace Database\Factories;

use App\Models\Page;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class PageFactory extends Factory
{
    protected string $model = Page::class;
    
    public function definition(): array
    {
        return [
            'title'     => Str::random(10),
            'slug'      => Str::random(10),
            'content'   => join('. ', $this->faker->sentences(5)),
            'published' => 1,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now()
        ];
    }
}
