<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        foreach (['admin' => 'Administrador Aurora', 'agent' => 'Atendente Aurora', 'customer' => 'Cliente Demonstração'] as $role => $name) {
            $email = $role.'@aurora.test';
            $user = User::where('email', $email)->first();
            if (! $user) {
                $password = config('airline.demo_passwords.'.$role);
                if (! $password || strlen($password) < 8) {
                    throw new \RuntimeException('Configure DEMO_'.strtoupper($role).'_PASSWORD no .env antes de executar os seeders de demonstração.');
                }
                $user = new User(['name' => $name, 'email' => $email, 'password' => $password]);
                $user->role = $role;
                $user->save();
            }
            if ($role === 'customer') {
                Customer::firstOrCreate(['user_id' => $user->id], ['name' => $name, 'email' => $email, 'document' => 'DEMO-0001', 'phone' => '11999990000']);
            }
        }
    }
}
