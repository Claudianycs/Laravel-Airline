<?php

namespace Database\Seeders;

use App\Models\Airport;
use Illuminate\Database\Seeder;

class AirportSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([['GRU', 'Aeroporto Internacional de Guarulhos', 'São Paulo'], ['GIG', 'Aeroporto Internacional do Galeão', 'Rio de Janeiro'], ['BSB', 'Aeroporto Internacional de Brasília', 'Brasília'], ['SSA', 'Aeroporto Internacional de Salvador', 'Salvador']] as [$iata,$name,$city]) {
            Airport::firstOrCreate(['iata' => $iata], ['name' => $name, 'city' => $city, 'country' => 'Brasil']);
        }
    }
}
