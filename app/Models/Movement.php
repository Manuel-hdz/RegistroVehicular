<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Models\User;

class Movement extends Model
{
    use HasFactory;

    protected $fillable = [
        'vehicle_id',
        'driver_id',
        'guard_out_id',
        'guard_in_id',
        'odometer_out',
        'fuel_out',
        'departed_at',
        'destination',
        'notes_out',
        'odometer_in',
        'fuel_in',
        'arrived_at',
        'notes_in',
        'status',
    ];

    protected $casts = [
        'departed_at' => 'datetime',
        'arrived_at' => 'datetime',
        'odometer_out' => 'integer',
        'odometer_in' => 'integer',
        'fuel_out' => 'integer',
        'fuel_in' => 'integer',
    ];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(Driver::class);
    }

    public function guardOut(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guard_out_id');
    }

    public function guardIn(): BelongsTo
    {
        return $this->belongsTo(User::class, 'guard_in_id');
    }
}
