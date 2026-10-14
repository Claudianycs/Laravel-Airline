<?php

namespace App\Http\Controllers;

use App\Models\Airline;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AirlineController extends CrudController
{
    protected string $model = Airline::class;

    protected string $resource = 'airlines';

    protected string $title = 'Companhias aéreas';

    protected array $search = ['name', 'iata', 'country'];

    protected function fields(): array
    {
        return ['name' => ['label' => 'Razão social / nome'], 'iata' => ['label' => 'Código IATA', 'maxlength' => 2], 'country' => ['label' => 'País'], 'email' => ['label' => 'E-mail de contato', 'type' => 'email'], 'active' => ['label' => 'Ativa para vendas', 'type' => 'select', 'options' => [1 => 'Sim', 0 => 'Não']]];
    }

    protected function rules(Request $request, ?Model $record = null): array
    {
        $request->merge(['iata' => strtoupper($request->string('iata')->trim()->toString())]);

        return ['name' => 'required|string|max:255', 'iata' => ['required', 'regex:/^[A-Z0-9]{2}$/', Rule::unique('airlines')->ignore($record?->id)], 'country' => 'required|string|max:255', 'email' => 'required|email|max:255', 'active' => 'required|boolean'];
    }
}
