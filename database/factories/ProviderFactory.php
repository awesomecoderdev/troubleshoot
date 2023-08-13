<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Provider>
 */
class ProviderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            // "name" => fake()->name(),
            // "category_id" => fake()->numberBetween(1, 20),
            // "provider_id" => fake()->numberBetween(1, 20),
            // "zone_id" => fake()->numberBetween(1, 20),
            // "price" => fake()->numberBetween(500, 2000),
            // "type" => fake()->randomElement([
            //     "fixed",
            //     "hourly"
            // ]),
            // "duration" =>  fake()->numberBetween(1, 20) . " Days",
            // "discount" => fake()->numberBetween(1, 20),
            // "short_description" => fake()->text(200),
            // "long_description" => fake()->text(500),
            // "tax" => fake()->numberBetween(10, 20),
            // "is_featured" => fake()->randomElement([
            //     true,
            //     false
            // ]),

            "zone_id" => fake()->numberBetween(1, 20),
            "company_name" =>  fake()->company(),
            "first_name" => fake()->firstName(),
            "last_name" => fake()->lastName(),
            "email" => fake()->safeEmail(),
            "password" => fake()->password(),
            "phone" => fake()->phoneNumber(),
            "identity_number" => fake()->phoneNumber(),
            "contact_person_name" => fake()->name(),
            "contact_person_phone" => fake()->phoneNumber(),
            "contact_email" => fake()->email(),
            // "image" => fake(),
            // "identity_image" => fake(),
            // "order_count" => fake(),
            // "service_man_count" => fake(),
            // "service_capacity_per_day" => fake(),
            // "rating_count" => fake(),
            // "avg_rating" => fake(),
            "commission_status" => fake()->boolean(),
            // "commission_percentage" => fake(),
            // "is_active" => fake(),
            // "is_approved" => fake(),
            "start" => fake()->dateTimeBetween('+10 days', '+ 2 months'),
            "end" =>  fake()->dateTimeBetween('+100 days', '+ 9 months'),
            "off_day" => [fake()->dayOfWeek()],
        ];
    }
}
