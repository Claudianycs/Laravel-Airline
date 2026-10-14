<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class UserController extends CrudController
{
    protected string $model = User::class;

    protected string $resource = 'users';

    protected string $title = 'Usuários';

    protected array $search = ['name', 'email', 'role'];

    protected function fields(): array
    {
        return ['name' => ['label' => 'Nome'], 'email' => ['label' => 'E-mail de acesso', 'type' => 'email'], 'role' => ['label' => 'Perfil', 'type' => 'select', 'options' => ['admin' => 'Administrador', 'agent' => 'Atendente', 'customer' => 'Cliente']], 'password' => ['label' => 'Senha (deixe vazia para manter ao editar)', 'type' => 'password', 'required' => false], 'password_confirmation' => ['label' => 'Confirme a senha', 'type' => 'password', 'required' => false]];
    }

    protected function rules(Request $request, ?Model $record = null): array
    {
        return ['name' => 'required|string|max:255', 'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($record?->id)], 'role' => ['required', Rule::in(['admin', 'agent', 'customer'])], 'password' => [$record ? 'nullable' : 'required', 'confirmed', Password::min(8)->letters()->numbers()]];
    }

    protected function persist(Model $record, array $data): Model
    {
        return DB::transaction(function () use ($record, $data) {
            $admins = User::where('role', 'admin')->orderBy('id')->lockForUpdate()->get();
            if ($record->exists) {
                $record = User::lockForUpdate()->findOrFail($record->id);
            }
            if ($record->exists && $record->role === 'admin' && $data['role'] !== 'admin' && ($admins->count() === 1 || $record->id === auth()->id())) {
                throw ValidationException::withMessages(['role' => 'Não é permitido remover seu próprio acesso administrativo ou o último administrador.']);
            }
            if (empty($data['password'])) {
                unset($data['password']);
            }
            $role = $data['role'];
            unset($data['role']);
            $record->fill($data);
            $record->role = $role;
            $record->save();
            if ($role === 'customer' && ! $record->customer()->exists()) {
                Customer::create(['user_id' => $record->id, 'name' => $record->name, 'email' => $record->email]);
            }

            return $record;
        }, 3);
    }
}
