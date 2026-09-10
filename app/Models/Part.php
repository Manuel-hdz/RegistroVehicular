<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Part extends Model
{
    use HasFactory;

    protected static function booted(): void
    {
        static::saving(function (Part $part): void {
            $part->identity_key = self::identityKey($part->name, $part->characteristics);
        });
    }

    protected $fillable = ['clave', 'name', 'characteristics', 'unit_cost', 'active', 'entry_type', 'entry_date', 'invoice_path'];

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

    public static function identityKey(string $name, mixed $characteristics = []): string
    {
        $normalizedName = (string) Str::of($name)
            ->ascii()
            ->lower()
            ->squish();

        return hash('sha256', $normalizedName.'|'.implode('|', self::normalizeCharacteristics($characteristics)));
    }

    /**
     * @return array<int, string>
     */
    public static function normalizeCharacteristics(mixed $characteristics): array
    {
        $values = is_array($characteristics)
            ? $characteristics
            : preg_split('/[\r\n,]+/', (string) $characteristics);

        return collect($values ?: [])
            ->map(fn ($value) => (string) Str::of((string) $value)->ascii()->lower()->squish())
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->all();
    }
}
