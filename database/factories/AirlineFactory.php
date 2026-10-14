<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class AirlineFactory extends Factory
{
    public function definition(): array
    {
        return ['name' => fake()->company().' Airlines', 'iata' => strtoupper(fake()->unique()->lexify('??')), 'country' => 'Brasil', 'email' => fake()->companyEmail(), 'active' => true];
    }
}
