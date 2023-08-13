<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Zone>
 */
class ZoneFactory extends Factory
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
            // "coordinates" => "POLYGON((90.33344153867201 23.832882521696,90.31558875546899 23.82032003714,90.33344153867201 23.778855212352,90.42819861875 23.797390368231,90.442961497168 23.821262265672,90.40965919003899 23.846071821094,90.35644416318399 23.865225318425,90.33344153867201 23.832882521696,90.33344153867201 23.832882521696))",
            // "status" => fake(),
        ];
    }
}
