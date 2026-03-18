<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RequisitionItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'requisition_id',
        'material_name',
        'quantity',
        'equipment_vehicle_id',
        'justification',
    ];

    protected $casts = [
        'quantity' => 'decimal:2',
    ];

    public function requisition(): BelongsTo
    {
        return $this->belongsTo(Requisition::class);
    }

    public function equipmentVehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class, 'equipment_vehicle_id');
    }
}
