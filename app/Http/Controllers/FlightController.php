<?php

namespace App\Http\Controllers;

use App\Models\Aircraft;
use App\Models\Airline;
use App\Models\Airport;
use App\Models\Flight;
use App\Services\FlightService;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class FlightController extends CrudController
{
    protected string $model = Flight::class;

    protected string $resource = 'flights';

    protected string $title = 'Voos';

    protected array $search = ['code', 'status'];

    protected array $with = ['airline', 'origin', 'destination', 'aircraft'];

    protected function fields(): array
    {
        $airports = Airport::orderBy('city')->get()->pluck('label', 'id')->all();

        return ['airline_id' => ['label' => 'Companhia aérea', 'type' => 'select', 'options' => Airline::orderBy('name')->get()->pluck('label', 'id')->all()], 'code' => ['label' => 'Número do voo'], 'origin_id' => ['label' => 'Origem', 'type' => 'select', 'options' => $airports], 'destination_id' => ['label' => 'Destino', 'type' => 'select', 'options' => $airports], 'aircraft_id' => ['label' => 'Aeronave', 'type' => 'select', 'options' => Aircraft::orderBy('registration')->get()->pluck('label', 'id')->all()], 'departure_at' => ['label' => 'Partida', 'type' => 'datetime-local'], 'arrival_at' => ['label' => 'Chegada', 'type' => 'datetime-local'], 'price' => ['label' => 'Preço em R$', 'type' => 'number', 'min' => '0.01', 'step' => '0.01'], 'status' => ['label' => 'Situação', 'type' => 'select', 'options' => ['scheduled' => 'Programado', 'completed' => 'Concluído', 'cancelled' => 'Cancelado']]];
    }

    protected function rules(Request $request, ?Model $record = null): array
    {
        return ['airline_id' => 'required|exists:airlines,id', 'code' => ['required', 'string', 'max:12', Rule::unique('flights')->ignore($record?->id)], 'origin_id' => 'required|exists:airports,id', 'destination_id' => 'required|exists:airports,id|different:origin_id', 'aircraft_id' => 'required|exists:aircraft,id', 'departure_at' => 'required|date'.(! $record ? '|after:now' : ''), 'arrival_at' => 'required|date|after:departure_at', 'price' => ['required', 'regex:/^\d{1,7}(\.\d{1,2})?$/', 'numeric', 'min:0.01', 'max:9999999.99'], 'status' => ['required', Rule::in($record ? ['scheduled', 'completed', 'cancelled'] : ['scheduled'])]];
    }

    protected function persist(Model $record, array $data): Model
    {
        [$whole,$cents] = array_pad(explode('.', (string) $data['price']), 2, '0');
        $data['price_cents'] = (int) $whole * 100 + (int) str_pad($cents, 2, '0');
        unset($data['price']);

        return app(FlightService::class)->save($record, $data);
    }
}
