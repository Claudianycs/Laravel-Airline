<?php

namespace App\Services;

use App\Models\Aircraft;
use App\Models\Flight;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class FlightService
{
    public function save(Flight $flight, array $data): Flight
    {
        return DB::transaction(function () use ($flight, $data) {
            if ($flight->exists) {
                $flight = Flight::query()->lockForUpdate()->findOrFail($flight->id);
            }
            if (Aircraft::query()->lockForUpdate()->findOrFail($data['aircraft_id'])->airline_id != $data['airline_id']) {
                throw ValidationException::withMessages(['aircraft_id' => 'A aeronave deve pertencer à companhia do voo.']);
            }
            if (! $flight->exists) {
                $flight->fill($data)->save();

                return $flight;
            }
            $flight->fill($data);
            if ($flight->tickets()->exists() && $flight->isDirty(['airline_id', 'origin_id', 'destination_id', 'aircraft_id', 'departure_at', 'arrival_at'])) {
                throw ValidationException::withMessages(['departure_at' => 'Um voo com passagens emitidas não pode alterar rota, horários ou aeronave.']);
            }
            if ($flight->getOriginal('status') !== 'scheduled' && $flight->isDirty('status')) {
                throw ValidationException::withMessages(['status' => 'Um voo encerrado ou cancelado não pode ser reaberto.']);
            }
            if ($flight->status === 'completed' && $flight->arrival_at->isFuture()) {
                throw ValidationException::withMessages(['status' => 'O voo só pode ser concluído após a chegada.']);
            }
            $flight->save();
            if ($flight->status === 'cancelled') {
                $flight->activeTickets()->update(['status' => 'cancelled']);
            }

            return $flight;
        }, 3);
    }
}
