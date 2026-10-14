<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class AirportFactory extends Factory
{
    public function definition(): array
    {
        return ['name' => 'Aeroporto '.fake()->city(), 'iata' => strtoupper(fake()->unique()->lexify('???')), 'city' => fake()->city(), 'country' => 'Brasil'];
    }
}
