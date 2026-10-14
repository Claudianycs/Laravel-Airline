<?php

namespace Database\Seeders;

use App\Models\Airline;
use Illuminate\Database\Seeder;

class AirlineSeeder extends Seeder
{
    public function run(): void
    {
        Airline::firstOrCreate(['iata' => 'AU'], ['name' => 'Aurora Airlines', 'country' => 'Brasil', 'email' => 'contato@aurora.test', 'active' => true]);
    }
}
