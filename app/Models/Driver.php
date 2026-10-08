<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Driver extends Model
{
    use HasFactory;

    public const LICENSE_TYPES = ['A', 'B', 'C', 'F'];

    protected $fillable = [
        'personnel_id',
        'name',
        'employee_number',
        'license',
        'license_expires_at',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
        'license_expires_at' => 'date',
    ];

    public function movements(): HasMany
    {
        return $this->hasMany(Movement::class);
    }

    public function personnel(): BelongsTo
    {
        return $this->belongsTo(Personnel::class);
    }
}
