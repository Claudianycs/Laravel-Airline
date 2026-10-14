<?php

namespace App\Http\Controllers;

use App\Models\Aircraft;
use App\Models\Airline;
use App\Models\Flight;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class AircraftController extends CrudController
{
    protected string $model = Aircraft::class;

    protected string $resource = 'aircraft';

    protected string $title = 'Aeronaves';

    protected array $search = ['registration', 'model'];

    protected function fields(): array
    {
        return ['airline_id' => ['label' => 'Companhia aérea', 'type' => 'select', 'options' => Airline::orderBy('name')->get()->pluck('label', 'id')->all()], 'registration' => ['label' => 'Matrícula'], 'model' => ['label' => 'Modelo'], 'capacity' => ['label' => 'Capacidade de passageiros', 'type' => 'number', 'min' => 1, 'max' => 1000]];
    }

    protected function rules(Request $request, ?Model $record = null): array
    {
        $request->merge(['registration' => strtoupper($request->string('registration')->trim()->toString())]);

        return ['airline_id' => 'required|exists:airlines,id', 'registration' => ['required', 'string', 'max:20', Rule::unique('aircraft')->ignore($record?->id)], 'model' => 'required|string|max:255', 'capacity' => 'required|integer|min:1|max:1000'];
    }

    protected function persist(Model $record, array $data): Model
    {
        return DB::transaction(function () use ($record, $data) {
            if ($record->exists) {
                $flights = Flight::where('aircraft_id', $record->id)->orderBy('id')->lockForUpdate()->get();
                foreach ($flights as $flight) {
                    if ($flight->activeTickets()->count() > $data['capacity']) {
                        throw ValidationException::withMessages(['capacity' => 'A capacidade não pode ser menor que as reservas e passagens ativas de um voo.']);
                    }
                }
                $record = Aircraft::lockForUpdate()->findOrFail($record->id);
                if ($flights->isNotEmpty() && $record->airline_id != $data['airline_id']) {
                    throw ValidationException::withMessages(['airline_id' => 'Aeronaves vinculadas a voos não podem mudar de companhia.']);
                }
            }

            return parent::persist($record, $data);
        }, 3);
    }
}
