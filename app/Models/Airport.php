<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Airport extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'iata', 'city', 'country'];

    public function departures(): HasMany
    {
        return $this->hasMany(Flight::class, 'origin_id');
    }

    public function arrivals(): HasMany
    {
        return $this->hasMany(Flight::class, 'destination_id');
    }

    public function getLabelAttribute(): string
    {
        return $this->iata.' — '.$this->city;
    }
}
