<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Address>
 */
class AddressFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            "customer_id" => fake()->numberBetween(1, 55),
            "street_one" => fake()->streetAddress(),
            "street_two" =>  fake()->streetAddress(),
            "apartment_name" =>  fake()->streetAddress(),
            "apartment_number" =>  fake()->numberBetween(1111, 9999),
            "city" => fake()->city(),
            "zip" => fake()->numberBetween(1111, 9999),
            "lat" => "23.747" . fake()->numberBetween(100, 600),
            "lng" => "90.376" . fake()->numberBetween(149, 849),
        ];
    }
}
