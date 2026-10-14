<?php

namespace App\Providers;

use App\Models\Aircraft;
use App\Models\Airline;
use App\Models\Airport;
use App\Models\Customer;
use App\Models\Flight;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void {}

    public function boot(): void
    {
        Paginator::useBootstrapFive();
        foreach ([Airline::class, Airport::class, Aircraft::class, Flight::class, Customer::class, Ticket::class, User::class] as $model) {
            Gate::policy($model, 'App\\Policies\\'.class_basename($model).'Policy');
        }
        Gate::define('manage-airline', fn (User $user) => $user->isStaff());
        Gate::define('view-sales', fn (User $user) => $user->isStaff());
        Gate::define('manage-users', fn (User $user) => $user->role === 'admin');
        Gate::define('buy-tickets', fn (User $user) => $user->customer !== null);
    }
}
