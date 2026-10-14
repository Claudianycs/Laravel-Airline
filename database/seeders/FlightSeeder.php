<?php

namespace Database\Seeders;

use App\Models\Aircraft;
use App\Models\Airport;
use App\Models\Flight;
use Illuminate\Database\Seeder;

class FlightSeeder extends Seeder
{
    public function run(): void
    {
        $aircraft = Aircraft::orderBy('id')->get();
        foreach ([['GRU', 'GIG', 32990], ['GIG', 'BSB', 45990], ['GRU', 'SSA', 69990], ['BSB', 'GRU', 38990], ['SSA', 'GIG', 57990], ['GIG', 'GRU', 30990]] as $i => [$origin,$destination,$price]) {
            $departure = now()->addDays($i + 2)->setTime(8 + $i, 0);
            Flight::firstOrCreate(['code' => 'AU'.(1001 + $i)], ['airline_id' => $aircraft[$i % $aircraft->count()]->airline_id, 'origin_id' => Airport::where('iata', $origin)->firstOrFail()->id, 'destination_id' => Airport::where('iata', $destination)->firstOrFail()->id, 'aircraft_id' => $aircraft[$i % $aircraft->count()]->id, 'departure_at' => $departure, 'arrival_at' => $departure->copy()->addHours(2), 'price_cents' => $price, 'status' => 'scheduled']);
        }
    }
}
