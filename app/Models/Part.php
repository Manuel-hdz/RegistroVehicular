<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Part extends Model
{
    use HasFactory;

    protected $fillable = ['clave','name','characteristics','unit_cost','active','entry_type','entry_date','invoice_path'];

    protected $casts = [
        'unit_cost' => 'decimal:2',
        'active' => 'boolean',
        'entry_date' => 'datetime',
        'characteristics' => 'array',
    ];

    public function repairs(): BelongsToMany
    {
        return $this->belongsToMany(Repair::class)->withPivot('quantity')->withTimestamps();
    }

    public function entryMaterials(): HasMany
    {
        return $this->hasMany(WarehouseEntryMaterial::class);
    }

    public function materialExits(): HasMany
    {
        return $this->hasMany(WarehouseMaterialExit::class);
    }

    public function latestMaterialExit(): HasOne
    {
        return $this->hasOne(WarehouseMaterialExit::class)->latestOfMany();
    }
}