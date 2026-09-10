<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class WarehouseMaterialExit extends Model
{
    use HasFactory;

    protected $fillable = [
        'cost_center_id',
        'part_id',
        'quantity',
        'exit_date',
        'source',
        'dispatched_by',
        'carried_by',
        'destination',
        'responsible',
        'invoice_path',
        'status',
        'delivered_at',
        'voucher_number',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
        'exit_date' => 'datetime',
        'delivered_at' => 'datetime',
    ];

    public function part(): BelongsTo
    {
        return $this->belongsTo(Part::class);
    }

    public function costCenter(): BelongsTo
    {
        return $this->belongsTo(CostCenter::class);
    }
}
