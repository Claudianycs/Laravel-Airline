<?php

namespace Database\Seeders;

use App\Models\Aircraft;
use App\Models\Airline;
use Illuminate\Database\Seeder;

class AircraftSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([['PR-AUR', 'Airbus A320', 180], ['PR-AUS', 'Boeing 737-800', 186], ['PR-AUT', 'Embraer E195', 118]] as [$registration,$model,$capacity]) {
            Aircraft::firstOrCreate(['registration' => $registration], compact('model', 'capacity') + ['airline_id' => Airline::where('iata', 'AU')->firstOrFail()->id]);
        }
    }
}
