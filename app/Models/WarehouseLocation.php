<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class WarehouseLocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'cost_center_id',
        'name',
        'description',
        'active',
        'created_by',
        'updated_by',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    protected static function booted(): void
    {
        static::saving(function (WarehouseLocation $location): void {
            $location->name = (string) Str::of($location->name)->squish();
            $location->normalized_name = self::normalizeName($location->name);
        });
    }

    public static function normalizeName(string $name): string
    {
        return (string) Str::of($name)->ascii()->lower()->squish();
    }

    public function costCenter(): BelongsTo
    {
        return $this->belongsTo(CostCenter::class);
    }

    public function entryMaterials(): HasMany
    {
        return $this->hasMany(WarehouseEntryMaterial::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function updater(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
