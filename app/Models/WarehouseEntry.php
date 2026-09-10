<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WarehouseEntry extends Model
{
    use HasFactory;

    protected $fillable = ['entry_key', 'entry_type', 'entry_date', 'invoice_path'];

    protected $casts = [
        'entry_date' => 'datetime',
    ];

    public function materials(): HasMany
    {
        return $this->hasMany(WarehouseEntryMaterial::class);
    }
}