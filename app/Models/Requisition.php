<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Requisition extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_REVIEWING = 'reviewing';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_PURCHASED = 'purchased';
    public const STATUS_DELIVERED = 'delivered';
    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'cost_center_id',
        'requester_name',
        'vehicle_id',
        'status',
    ];

    public function costCenter(): BelongsTo
    {
        return $this->belongsTo(CostCenter::class);
    }

    public function vehicle(): BelongsTo
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(RequisitionItem::class);
    }

    public function getFolioAttribute(): string
    {
        $date = optional($this->created_at)->format('Ymd') ?? now()->format('Ymd');

        return 'REQ-' . $date . '-' . str_pad((string) $this->id, 4, '0', STR_PAD_LEFT);
    }

    public static function statuses(): array
    {
        return [
            self::STATUS_PENDING => 'Pendiente',
            self::STATUS_REVIEWING => 'En revision',
            self::STATUS_APPROVED => 'Autorizada',
            self::STATUS_PURCHASED => 'Comprada',
            self::STATUS_DELIVERED => 'Entregada',
            self::STATUS_REJECTED => 'Rechazada',
        ];
    }

    public function getStatusLabelAttribute(): string
    {
        return self::statuses()[$this->status] ?? ucfirst((string) $this->status);
    }
}
