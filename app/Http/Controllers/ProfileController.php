<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function edit(Request $request)
    {
        $customer = $request->user()->customer;
        abort_unless($customer, 404);
        Gate::authorize('update', $customer);

        return view('profile.edit', compact('customer'));
    }

    public function update(Request $request)
    {
        $customer = $request->user()->customer;
        abort_unless($customer, 404);
        Gate::authorize('update', $customer);
        $data = $request->validate(['name' => 'required|string|max:255', 'email' => 'required|email|max:255', 'document' => ['required', 'string', 'max:30', Rule::unique('customers')->ignore($customer->id)], 'phone' => 'nullable|string|max:30', 'birth_date' => 'nullable|date|before:today']);
        $customer->update($data);

        return redirect()->route('profile.edit')->with('success', 'Perfil de passageiro atualizado.');
    }
}
