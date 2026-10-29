<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MaintenanceBacklogItem extends Model
{
    public const SYSTEMS = [
        'hidraulico' => 'Hidráulico', 'neumatico' => 'Neumático',
        'mecanico' => 'Mecánico', 'electrico' => 'Eléctrico',
        'electronico' => 'Electrónico', 'motor' => 'Motor',
        'transmision' => 'Transmisión', 'frenos' => 'Frenos',
        'enfriamiento' => 'Enfriamiento', 'lubricacion' => 'Lubricación', 'otro' => 'Otro',
    ];

    public const CRITICALITIES = ['bajo' => 'Bajo', 'medio' => 'Medio', 'alto' => 'Alto'];

    public const STATUSES = ['required' => 'Requerida', 'purchased' => 'Comprada', 'used' => 'Utilizada'];

    protected $fillable = [
        'vehicle_id', 'cost_center_id', 'created_by', 'quantity', 'unit',
        'description', 'unit_cost', 'total_cost', 'system_type', 'criticality', 'status',
    ];

    protected $casts = ['quantity' => 'decimal:2', 'unit_cost' => 'decimal:2', 'total_cost' => 'decimal:2'];

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function costCenter(): BelongsTo
    {
        return $this->belongsTo(CostCenter::class);
    }
}
