<?php

namespace Database\Factories;

use App\Models\Aircraft;
use App\Models\Airport;
use Illuminate\Database\Eloquent\Factories\Factory;

class FlightFactory extends Factory
{
    public function definition(): array
    {
        return ['code' => fake()->unique()->bothify('AU####'), 'origin_id' => Airport::factory(), 'destination_id' => Airport::factory(), 'aircraft_id' => Aircraft::factory(), 'airline_id' => fn (array $attributes) => Aircraft::findOrFail($attributes['aircraft_id'])->airline_id, 'departure_at' => now()->addDays(5), 'arrival_at' => now()->addDays(5)->addHours(2), 'price_cents' => fake()->numberBetween(10000, 150000), 'status' => 'scheduled'];
    }
}
