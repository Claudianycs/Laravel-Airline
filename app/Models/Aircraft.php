<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Aircraft extends Model
{
    use HasFactory;

    public function airline(): BelongsTo
    {
        return $this->belongsTo(Airline::class);
    }

    protected $fillable = ['airline_id', 'registration', 'model', 'capacity'];

    protected $table = 'aircraft';

    protected $casts = ['capacity' => 'integer'];

    public function flights(): HasMany
    {
        return $this->hasMany(Flight::class);
    }

    public function getLabelAttribute(): string
    {
        return $this->registration.' — '.$this->model;
    }
}
