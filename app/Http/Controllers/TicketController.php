<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Flight;
use App\Models\Ticket;
use App\Services\TicketService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        Gate::authorize('viewAny', Ticket::class);
        $query = Ticket::with(['flight.origin', 'flight.destination', 'customer', 'seller'])->latest('id');
        if (! $request->user()->isStaff()) {
            $query->whereHas('customer', fn ($q) => $q->where('user_id', $request->user()->id));
        }
        if ($request->filled('status')) {
            $request->validate(['status' => Rule::in(['reserved', 'paid', 'cancelled'])]);
            $query->where('status', $request->status);
        }
        if ($request->filled('q')) {
            $query->where('reference', 'like', '%'.mb_substr($request->string('q')->toString(), 0, 100).'%');
        }

        return view('tickets.index', ['tickets' => $query->paginate(12)->withQueryString()]);
    }

    public function create(Request $request)
    {
        Gate::authorize('manage-airline');

        return view('tickets.form', ['ticket' => new Ticket, 'flights' => Flight::with(['origin', 'destination', 'aircraft'])->withCount('activeTickets')->where('status', 'scheduled')->where('departure_at', '>', now())->orderBy('departure_at')->get(), 'customers' => Customer::orderBy('name')->get()]);
    }

    public function store(Request $request, TicketService $service)
    {
        Gate::authorize('create', Ticket::class);
        $data = $request->validate(['flight_id' => 'required|exists:flights,id', 'customer_id' => [$request->user()->isStaff() ? 'required' : 'prohibited', 'exists:customers,id'], 'status' => ['required', Rule::in(['reserved', 'paid'])], 'payment_method' => ['nullable', 'required_if:status,paid', Rule::in($request->user()->isStaff() ? ['pix', 'card', 'cash'] : ['pix', 'card'])]]);
        $ticket = $service->issue($request->user(), $data);

        return redirect()->route('tickets.show', $ticket)->with('success', $ticket->status === 'paid' ? 'Compra registrada com pagamento simulado. Nenhuma cobrança real foi realizada.' : 'Reserva criada. O lugar ficará reservado até o cancelamento ou a partida.');
    }

    public function show(Ticket $ticket)
    {
        Gate::authorize('view', $ticket);

        return view('tickets.show', ['ticket' => $ticket->load(['flight.origin', 'flight.destination', 'flight.aircraft', 'customer', 'seller'])]);
    }

    public function edit(Ticket $ticket)
    {
        Gate::authorize('update', $ticket);

        return view('tickets.form', ['ticket' => $ticket->load(['flight.origin', 'flight.destination', 'customer'])]);
    }

    public function update(Request $request, Ticket $ticket, TicketService $service)
    {
        Gate::authorize('update', $ticket);
        $data = $request->validate(['status' => ['required', Rule::in(['paid', 'cancelled'])], 'payment_method' => ['nullable', Rule::in($request->user()->isStaff() ? ['pix', 'card', 'cash'] : ['pix', 'card'])], 'flight_id' => 'prohibited', 'customer_id' => 'prohibited', 'price_cents' => 'prohibited']);
        $service->change($ticket, $data);

        return redirect()->route('tickets.show', $ticket)->with('success', 'Passagem atualizada. Operação financeira simulada.');
    }

    public function destroy(Ticket $ticket)
    {
        Gate::authorize('delete', $ticket);
        $ticket->delete();

        return redirect()->route('tickets.index')->with('success', 'Passagem cancelada excluída.');
    }
}
