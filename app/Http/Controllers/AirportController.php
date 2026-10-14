<?php

namespace App\Http\Controllers;

use App\Models\Airport;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AirportController extends CrudController
{
    protected string $model = Airport::class;

    protected string $resource = 'airports';

    protected string $title = 'Aeroportos';

    protected array $search = ['name', 'iata', 'city', 'country'];

    protected function fields(): array
    {
        return ['name' => ['label' => 'Nome'], 'iata' => ['label' => 'Código IATA', 'maxlength' => 3], 'city' => ['label' => 'Cidade'], 'country' => ['label' => 'País']];
    }

    protected function rules(Request $request, ?Model $record = null): array
    {
        $request->merge(['iata' => strtoupper($request->string('iata')->trim()->toString())]);

        return ['name' => 'required|string|max:255', 'iata' => ['required', 'regex:/^[A-Z]{3}$/', Rule::unique('airports')->ignore($record?->id)], 'city' => 'required|string|max:255', 'country' => 'required|string|max:255'];
    }
}
