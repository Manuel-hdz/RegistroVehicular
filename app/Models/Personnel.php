<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Personnel extends Model
{
    use HasFactory;

    protected $fillable = [
        'employee_number',
        'first_name',
        'last_name',
        'middle_name',
        'curp',
        'rfc',
        'nss',
        'department',
        'position',
        'hire_date',
        'phone',
        'email',
        'address',
        'emergency_contact_name',
        'emergency_contact_phone',
        'photo_path',
        'active',
        'terminated_at',
    ];

    protected $casts = [
        'hire_date' => 'date',
        'active' => 'boolean',
        'terminated_at' => 'date',
    ];

    public function cardexEntries(): HasMany
    {
        return $this->hasMany(PersonnelCardexEntry::class);
    }

    public function drivers(): HasMany
    {
        return $this->hasMany(Driver::class);
    }

    public function mechanics(): HasMany
    {
        return $this->hasMany(Mechanic::class);
    }

    public function getFullNameAttribute(): string
    {
        return trim(implode(' ', array_filter([
            $this->first_name,
            $this->last_name,
            $this->middle_name,
        ])));
    }

    public function getPhotoUrlAttribute(): ?string
    {
        if (!$this->photo_path) {
            return null;
        }

        if (str_starts_with($this->photo_path, 'images/')) {
            return asset($this->photo_path);
        }

        return route('personnel.photo', $this);
    }
}
