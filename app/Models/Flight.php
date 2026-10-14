<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Flight extends Model
{
    use HasFactory;

    public function airline(): BelongsTo
    {
        return $this->belongsTo(Airline::class);
    }

    protected $fillable = ['airline_id', 'code', 'origin_id', 'destination_id', 'aircraft_id', 'departure_at', 'arrival_at', 'price_cents', 'status'];

    protected function casts(): array
    {
        return ['departure_at' => 'datetime', 'arrival_at' => 'datetime', 'price_cents' => 'integer'];
    }

    public function origin(): BelongsTo
    {
        return $this->belongsTo(Airport::class, 'origin_id');
    }

    public function destination(): BelongsTo
    {
        return $this->belongsTo(Airport::class, 'destination_id');
    }

    public function aircraft(): BelongsTo
    {
        return $this->belongsTo(Aircraft::class);
    }

    public function tickets(): HasMany
    {
        return $this->hasMany(Ticket::class);
    }

    public function activeTickets(): HasMany
    {
        return $this->tickets()->whereIn('status', ['reserved', 'paid']);
    }

    public function getAvailableSeatsAttribute(): int
    {
        return max(0, $this->aircraft->capacity - ($this->active_tickets_count ?? $this->activeTickets()->count()));
    }

    public function getLabelAttribute(): string
    {
        return $this->code.' — '.$this->origin->iata.' → '.$this->destination->iata;
    }
}
