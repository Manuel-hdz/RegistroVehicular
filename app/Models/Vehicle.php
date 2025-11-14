<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'plate',
        'identifier',
        'vtype',
        'model',
        'year',
        'active',
        'availability',
        'maintenance_note',
    ];

    protected $casts = [
        'active' => 'boolean',
        'year' => 'integer',
    ];

    public function movements(): HasMany
    {
        return $this->hasMany(Movement::class);
    }
}
