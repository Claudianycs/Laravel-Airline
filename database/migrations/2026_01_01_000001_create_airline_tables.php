<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $t) {
            $t->enum('role', ['admin', 'agent', 'customer'])->default('customer');
        });
        Schema::create('airlines', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->char('iata', 2)->unique();
            $t->string('country');
            $t->string('email');
            $t->boolean('active')->default(true);
            $t->timestamps();
        });
        Schema::create('airports', function (Blueprint $t) {
            $t->id();
            $t->string('name');
            $t->char('iata', 3)->unique();
            $t->string('city');
            $t->string('country');
            $t->timestamps();
        });
        Schema::create('aircraft', function (Blueprint $t) {
            $t->id();
            $t->foreignId('airline_id')->constrained()->restrictOnDelete();
            $t->string('registration', 20)->unique();
            $t->string('model');
            $t->unsignedSmallInteger('capacity');
            $t->timestamps();
        });
        Schema::create('customers', function (Blueprint $t) {
            $t->id();
            $t->foreignId('user_id')->nullable()->unique()->constrained()->restrictOnDelete();
            $t->string('name');
            $t->string('email');
            $t->string('document', 30)->nullable()->unique();
            $t->string('phone', 30)->nullable();
            $t->date('birth_date')->nullable();
            $t->timestamps();
        });
        Schema::create('flights', function (Blueprint $t) {
            $t->id();
            $t->foreignId('airline_id')->constrained()->restrictOnDelete();
            $t->string('code', 12)->unique();
            $t->foreignId('origin_id')->constrained('airports')->restrictOnDelete();
            $t->foreignId('destination_id')->constrained('airports')->restrictOnDelete();
            $t->foreignId('aircraft_id')->constrained('aircraft')->restrictOnDelete();
            $t->dateTime('departure_at');
            $t->dateTime('arrival_at');
            $t->unsignedInteger('price_cents');
            $t->enum('status', ['scheduled', 'completed', 'cancelled'])->default('scheduled');
            $t->timestamps();
            $t->index(['status', 'departure_at']);
        });
        Schema::create('tickets', function (Blueprint $t) {
            $t->id();
            $t->string('reference', 12)->unique();
            $t->foreignId('flight_id')->constrained()->restrictOnDelete();
            $t->foreignId('customer_id')->constrained()->restrictOnDelete();
            $t->foreignId('sold_by')->nullable()->constrained('users')->restrictOnDelete();
            $t->unsignedInteger('price_cents');
            $t->enum('status', ['reserved', 'paid', 'cancelled'])->default('reserved');
            $t->enum('payment_method', ['pix', 'card', 'cash'])->nullable();
            $t->dateTime('paid_at')->nullable();
            $t->timestamps();
            $t->index(['flight_id', 'status']);
            $t->index(['customer_id', 'flight_id']);
        });
    }

    public function down(): void
    {
        foreach (['tickets', 'flights', 'customers', 'aircraft', 'airports', 'airlines'] as $table) {
            Schema::dropIfExists($table);
        }
        Schema::table('users', function (Blueprint $t) {
            $t->dropColumn('role');
        });
    }
};
