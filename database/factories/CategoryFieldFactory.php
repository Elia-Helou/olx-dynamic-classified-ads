<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CategoryField>
 */
class CategoryFieldFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = \App\Models\CategoryField::class;

    public function definition(): array
    {
        return [
            'category_id' => \App\Models\Category::factory(),
            'external_id' => fake()->unique()->word(),
            'name' => fake()->words(2, true),
            'field_type' => fake()->randomElement(['string', 'integer', 'float', 'boolean', 'select', 'radio']),
            'is_required' => false,
            'min_value' => null,
            'max_value' => null,
        ];
    }
}
