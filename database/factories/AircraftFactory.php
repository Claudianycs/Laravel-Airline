<?php

namespace Database\Factories;

use App\Models\Airline;
use Illuminate\Database\Eloquent\Factories\Factory;

class AircraftFactory extends Factory
{
    public function definition(): array
    {
        return ['airline_id' => Airline::factory(), 'registration' => 'PR-'.strtoupper(fake()->unique()->lexify('???')), 'model' => fake()->randomElement(['Airbus A320', 'Boeing 737', 'Embraer E195']), 'capacity' => fake()->numberBetween(80, 180)];
    }
}
