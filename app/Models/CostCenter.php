<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CostCenter extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'active',
    ];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function requisitions(): HasMany
    {
        return $this->hasMany(Requisition::class);
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)->withTimestamps();
    }

    public function warehouseEntries(): HasMany
    {
        return $this->hasMany(WarehouseEntry::class);
    }

    public function warehouseMaterialExits(): HasMany
    {
        return $this->hasMany(WarehouseMaterialExit::class);
    }

    public function warehouseLocations(): HasMany
    {
        return $this->hasMany(WarehouseLocation::class);
    }
}
