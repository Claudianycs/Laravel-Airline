<?php

namespace Database\Seeders;

use App\Models\Customer;
use Illuminate\Database\Seeder;

class CustomerSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([['PASS-1001', 'Mariana Silva', 'mariana@example.test'], ['PASS-1002', 'Rafael Costa', 'rafael@example.test'], ['PASS-1003', 'Beatriz Souza', 'beatriz@example.test']] as [$document,$name,$email]) {
            Customer::firstOrCreate(['document' => $document], compact('name', 'email'));
        }
    }
}
