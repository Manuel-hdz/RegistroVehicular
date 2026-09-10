<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EquipmentCustody extends Model
{
    public const TYPE_LABELS = [
        'laptop' => 'Laptops',
        'celular' => 'Celulares',
        'lampara' => 'Lamparas',
        'epp' => 'EPP',
    ];

    protected $fillable = [
        'equipment_type',
        'brand',
        'model',
        'serial_number',
        'accessories',
        'assigned_at',
        'responsible',
        'registered_by_user_id',
        'registered_by_username',
        'notification_email',
        'physical_record_path',
        'cancelled_at',
        'cancelled_by_user_id',
    ];

    protected function casts(): array
    {
        return [
            'assigned_at' => 'date',
            'cancelled_at' => 'datetime',
        ];
    }

    public static function typeLabels(): array
    {
        return self::TYPE_LABELS;
    }

    public function registeredBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registered_by_user_id');
    }

    public function cancelledBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cancelled_by_user_id');
    }
}
