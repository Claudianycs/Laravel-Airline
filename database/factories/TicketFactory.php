<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Flight;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

class TicketFactory extends Factory
{
    public function definition(): array
    {
        return ['reference' => strtoupper(Str::random(10)), 'flight_id' => Flight::factory(), 'customer_id' => Customer::factory(), 'sold_by' => null, 'price_cents' => 35000, 'status' => 'reserved', 'payment_method' => null, 'paid_at' => null];
    }

    public function paid(): static
    {
        return $this->state(fn () => ['status' => 'paid', 'payment_method' => 'pix', 'paid_at' => now()]);
    }

    public function cancelled(): static
    {
        return $this->state(fn () => ['status' => 'cancelled']);
    }
}
