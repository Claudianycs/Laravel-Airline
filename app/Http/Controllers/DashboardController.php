<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Flight;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class DashboardController extends Controller
{
    public function __invoke(Request $request)
    {
        $tickets = Ticket::query();
        if (! $request->user()->isStaff()) {
            $tickets->whereHas('customer', fn ($q) => $q->where('user_id', $request->user()->id));
        }
        $metrics = ['flights' => Flight::where('status', 'scheduled')->where('departure_at', '>', now())->count(), 'tickets' => (clone $tickets)->whereIn('status', ['reserved', 'paid'])->count(), 'total' => (clone $tickets)->where('status', 'paid')->sum('price_cents'), 'customers' => $request->user()->isStaff() ? Customer::count() : null];

        return view('dashboard', ['metrics' => $metrics, 'recent' => $tickets->with(['flight.origin', 'flight.destination', 'customer'])->latest()->limit(5)->get()]);
    }

    public function sales()
    {
        Gate::authorize('view-sales');
        $sales = Ticket::with(['flight', 'customer', 'seller'])->where('status', 'paid')->latest('paid_at')->paginate(20);
        $total = Ticket::where('status', 'paid')->sum('price_cents');

        return view('sales.index', compact('sales', 'total'));
    }
}
