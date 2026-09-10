<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WarehouseEntryMaterial extends Model
{
    use HasFactory;

    protected $fillable = ['warehouse_entry_id', 'part_id', 'warehouse_location_id', 'quantity'];

    protected $casts = [
        'quantity' => 'decimal:2',
    ];

    public function entry(): BelongsTo
    {
        return $this->belongsTo(WarehouseEntry::class, 'warehouse_entry_id');
    }

    public function part(): BelongsTo
    {
        return $this->belongsTo(Part::class);
    }

    public function location(): BelongsTo
    {
        return $this->belongsTo(WarehouseLocation::class, 'warehouse_location_id');
    }
}
