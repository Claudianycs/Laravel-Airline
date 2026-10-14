<?php

namespace App\Http\Controllers;

use App\Models\Airport;
use App\Models\Flight;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class MarketController extends Controller
{
    public function index(Request $request)
    {
        $data = $request->validate(['origin_id' => 'nullable|exists:airports,id', 'destination_id' => 'nullable|exists:airports,id', 'date' => 'nullable|date_format:Y-m-d']);
        $query = Flight::with(['airline', 'origin', 'destination', 'aircraft'])->withCount('activeTickets')->whereHas('airline', fn ($q) => $q->where('active', true))->where('status', 'scheduled')->where('departure_at', '>', now())->orderBy('departure_at');
        foreach (['origin_id', 'destination_id'] as $field) {
            if (! empty($data[$field])) {
                $query->where($field, $data[$field]);
            }
        }
        if (! empty($data['date'])) {
            $query->whereDate('departure_at', $data['date']);
        }

        return view('market.index', ['flights' => $query->paginate(9)->withQueryString(), 'airports' => Airport::orderBy('city')->get()]);
    }

    public function show(Flight $flight)
    {
        Gate::authorize('buy-tickets');
        abort_unless($flight->airline->active && $flight->status === 'scheduled' && $flight->departure_at->isFuture(), 404);

        return view('market.checkout', ['flight' => $flight->load(['origin', 'destination', 'aircraft']), 'customer' => auth()->user()->customer]);
    }
}
