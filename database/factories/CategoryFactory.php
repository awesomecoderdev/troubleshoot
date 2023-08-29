<?php

namespace Database\Factories;

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
    public function definition(): array
    {
        return [
            "parent_id" => fake()->numberBetween(1, 5),
            "name" => fake()->name(),
            // 'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            "position" => fake()->randomDigitNotNull(),
            "zone_id" => fake()->numberBetween(1, 2),
            // "is_active" => fake(),
            // "is_featured" => fake(),
        ];
    }
}
