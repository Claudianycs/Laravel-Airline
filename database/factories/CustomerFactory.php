<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CustomerFactory extends Factory
{
    public function definition(): array
    {
        return ['name' => fake()->name(), 'email' => fake()->safeEmail(), 'document' => fake()->unique()->numerify('###########'), 'phone' => fake()->phoneNumber(), 'birth_date' => fake()->dateTimeBetween('-80 years', '-18 years'), 'user_id' => null];
    }

    public function linked(): static
    {
        return $this->state(fn () => ['user_id' => User::factory()]);
    }
}
