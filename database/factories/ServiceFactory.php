<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Service>
 */
class ServiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            "name" => fake()->name(),
            // "parent_id" => "required",
            "category_id" => fake()->numberBetween(1, 20),
            "provider_id" => fake()->numberBetween(1, 20),
            "zone_id" => fake()->numberBetween(1, 20),
            "price" => fake()->numberBetween(500, 2000),
            "type" => fake()->randomElement([
                "fixed",
                "hourly"
            ]),
            "duration" =>  fake()->numberBetween(1, 20) . " Days",
            // 'image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            "discount" => fake()->numberBetween(1, 20),
            // 'status' => 'boolean', // Validate status field
            "short_description" => fake()->text(200),
            "long_description" => fake()->text(500),
            "tax" => fake()->numberBetween(10, 20),
            // "order_count" => "required",
            // "rating_count" => "required",
            // "avg_rating" => "required",
            "is_featured" => fake()->randomElement([
                true,
                false
            ]),
            // "by_admin" => "required",
        ];
    }
}
