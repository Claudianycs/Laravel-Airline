<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Flight;
use App\Models\Ticket;
use App\Models\User;
use App\Services\TicketService;
use Illuminate\Database\Seeder;

class TicketSeeder extends Seeder
{
    public function run(): void
    {
        $agent = User::where('email', 'agent@aurora.test')->firstOrFail();
        $customer = Customer::where('document', 'PASS-1001')->firstOrFail();
        $flight = Flight::where('code', 'AU1001')->firstOrFail();
        if ($flight->departure_at->isFuture() && $flight->status === 'scheduled' && ! Ticket::where('flight_id', $flight->id)->where('customer_id', $customer->id)->exists()) {
            app(TicketService::class)->issue($agent, ['flight_id' => $flight->id, 'customer_id' => $customer->id, 'status' => 'paid', 'payment_method' => 'pix']);
        }
    }
}
