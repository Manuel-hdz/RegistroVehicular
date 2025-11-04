<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Driver extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'employee_number',
        'license',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function movements(): HasMany
    {
        return $this->hasMany(Movement::class);
    }
}

