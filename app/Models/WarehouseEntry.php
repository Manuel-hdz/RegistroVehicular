<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class WarehouseEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'cost_center_id',
        'registered_by_user_id',
        'registered_by_username',
        'entry_key',
        'entry_type',
        'entry_date',
        'invoice_path',
    ];

    protected $casts = [
        'entry_date' => 'datetime',
    ];

    public function materials(): HasMany
    {
        return $this->hasMany(WarehouseEntryMaterial::class);
    }

    public function costCenter(): BelongsTo
    {
        return $this->belongsTo(CostCenter::class);
    }

    public function registeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registered_by_user_id');
    }
}
