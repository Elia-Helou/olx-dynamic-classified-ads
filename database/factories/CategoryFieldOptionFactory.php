<?php

namespace Database\Factories;

use App\Models\CategoryField;
use App\Models\CategoryFieldOption;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\CategoryFieldOption>
 */
class CategoryFieldOptionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    protected $model = CategoryFieldOption::class;

    public function definition(): array
    {
        return [
            'category_field_id' => CategoryField::factory(),
            'olx_id' => fake()->unique()->numberBetween(1, 10000),
            'option_value' => fake()->word(),
            'option_label' => fake()->words(2, true),
            'parent_olx_id' => null,
        ];
    }
}
