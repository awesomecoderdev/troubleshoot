<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Booking>
 */
class BookingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            "provider_id" => fake()->numberBetween(1, 10),
            "address_id" =>  fake()->numberBetween(1, 10),
            "customer_id" =>  fake()->numberBetween(1, 10),
            "coupon_id" =>  fake()->numberBetween(1, 10),
            "handyman_id" => fake()->numberBetween(1, 10),
            "campaign_id" =>  fake()->numberBetween(1, 10),
            "service_id" => fake()->numberBetween(1, 10),
            "category_id" => fake()->numberBetween(1, 10),
            "zone_id" => fake()->numberBetween(1, 10),
            // "status" => fake(),
            // "is_paid"=> fake(),
            // "payment_method" => fake(),
            "total_amount" => fake()->numberBetween(100, 500),
            "total_tax" => 0,
            "total_discount" => fake()->numberBetween(1, 10),
            "additional_charge" => fake()->numberBetween(20, 50),
            // "is_rated"=> fake(),
        ];
    }
}
