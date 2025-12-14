<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Category>
 */
class CategoryFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = Category::class;

    public function definition(): array
    {
        return [
            'olx_id' => fake()->unique()->numberBetween(1, 10000),
            'external_id' => fake()->unique()->numerify('ext-####'),
            'name' => fake()->words(2, true),
            'name_ar' => null,
            'slug' => fake()->slug(),
            'level' => 0,
            'parent_id' => null,
            'purpose' => null,
            'roles' => null,
        ];
    }
}
