<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\Flight;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class TicketService
{
    public function issue(User $buyer, array $data): Ticket
    {
        return DB::transaction(function () use ($buyer, $data) {
            $flight = Flight::query()->lockForUpdate()->findOrFail($data['flight_id']);
            $this->assertOpen($flight);
            $customerId = $buyer->isStaff() ? $data['customer_id'] : $buyer->customer?->id;
            if (! $customerId) {
                $this->fail('customer_id', 'Cadastre seu perfil de passageiro antes de comprar.');
            }
            $customer = Customer::query()->lockForUpdate()->findOrFail($customerId);
            if (! $customer->document) {
                $this->fail('customer_id', 'Informe o documento do passageiro antes de emitir a passagem.');
            }
            if ($flight->activeTickets()->where('customer_id', $customerId)->exists()) {
                $this->fail('flight_id', 'Este passageiro já possui uma reserva ativa neste voo.');
            }
            if ($flight->activeTickets()->count() >= $flight->aircraft->capacity) {
                $this->fail('flight_id', 'Não há lugares disponíveis neste voo.');
            }
            $status = $data['status'] ?? 'paid';
            $method = $status === 'paid' ? ($data['payment_method'] ?? null) : null;
            if ($status === 'paid' && ! $method) {
                $this->fail('payment_method', 'Selecione a forma de pagamento simulado.');
            }

            return Ticket::create([
                'reference' => strtoupper(Str::random(10)), 'flight_id' => $flight->id, 'customer_id' => $customerId,
                'sold_by' => $buyer->isStaff() ? $buyer->id : null, 'price_cents' => $flight->price_cents,
                'status' => $status, 'payment_method' => $method, 'paid_at' => $status === 'paid' ? now() : null,
            ]);
        }, 3);
    }

    public function change(Ticket $ticket, array $data): Ticket
    {
        return DB::transaction(function () use ($ticket, $data) {
            // All inventory mutations lock the flight first, including cancellation.
            $flight = Flight::query()->lockForUpdate()->findOrFail($ticket->flight_id);
            $ticket = Ticket::query()->lockForUpdate()->findOrFail($ticket->id);
            $status = $data['status'];
            if ($ticket->status === 'cancelled') {
                $this->fail('status', 'Uma passagem cancelada não pode ser reativada. Emita uma nova passagem.');
            }
            if ($status === 'cancelled') {
                if ($flight->departure_at->isPast() && $flight->status !== 'cancelled') {
                    $this->fail('status', 'O prazo de cancelamento termina na partida.');
                }
                $ticket->update(['status' => 'cancelled']);
            } elseif ($status === 'paid' && $ticket->status === 'reserved') {
                $this->assertOpen($flight);
                if (empty($data['payment_method'])) {
                    $this->fail('payment_method', 'Selecione a forma de pagamento simulado.');
                }
                $ticket->update(['status' => 'paid', 'payment_method' => $data['payment_method'], 'paid_at' => now()]);
            } else {
                $this->fail('status', 'Escolha um pagamento de reserva ou um cancelamento.');
            }

            return $ticket->refresh();
        }, 3);
    }

    private function assertOpen(Flight $flight): void
    {
        if (! $flight->airline->active || $flight->status !== 'scheduled' || $flight->departure_at->isPast()) {
            $this->fail('flight_id', 'Este voo não está disponível para venda.');
        }
    }

    private function fail(string $field, string $message): never
    {
        throw ValidationException::withMessages([$field => $message]);
    }
}
