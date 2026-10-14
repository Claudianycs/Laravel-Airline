<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CustomerController extends CrudController
{
    protected string $model = Customer::class;

    protected string $resource = 'customers';

    protected string $title = 'Passageiros';

    protected array $search = ['name', 'email', 'document'];

    protected function fields(): array
    {
        return ['name' => ['label' => 'Nome completo'], 'email' => ['label' => 'E-mail de contato', 'type' => 'email'], 'document' => ['label' => 'Documento', 'maxlength' => 30], 'phone' => ['label' => 'Telefone', 'required' => false], 'birth_date' => ['label' => 'Data de nascimento', 'type' => 'date', 'required' => false]];
    }

    protected function rules(Request $request, ?Model $record = null): array
    {
        return ['name' => 'required|string|max:255', 'email' => 'required|email|max:255', 'document' => ['required', 'string', 'max:30', Rule::unique('customers')->ignore($record?->id)], 'phone' => 'nullable|string|max:30', 'birth_date' => 'nullable|date|before:today'];
    }
}
