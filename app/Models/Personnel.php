<?php

namespace App\Models;

use Carbon\Carbon;
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
        'marital_status',
        'sex',
        'birth_date',
        'department',
        'position',
        'hire_date',
        'account_number',
        'account_type',
        'phone',
        'email',
        'address',
        'emergency_contact_name',
        'emergency_contact_phone',
        'photo_path',
        'active',
        'terminated_at',
        'pending_vacation_days',
        'vacation_years_awarded',
    ];

    protected $casts = [
        'hire_date' => 'date',
        'birth_date' => 'date',
        'active' => 'boolean',
        'terminated_at' => 'date',
        'pending_vacation_days' => 'integer',
        'vacation_years_awarded' => 'integer',
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

    public function getSeniorityLabelAttribute(): string
    {
        if (!$this->active) {
            return 'No aplica por baja';
        }

        if (!$this->hire_date) {
            return 'Sin fecha de ingreso';
        }

        $startDate = $this->hire_date->copy()->startOfDay();
        $today = Carbon::today();

        if ($startDate->gt($today)) {
            return '0 dias';
        }

        $diff = $startDate->diff($today);
        $parts = [];

        if ($diff->y > 0) {
            $parts[] = $diff->y . ' ' . ($diff->y === 1 ? 'año' : 'años');
        }
        if ($diff->m > 0) {
            $parts[] = $diff->m . ' ' . ($diff->m === 1 ? 'mes' : 'meses');
        }
        if ($diff->d > 0 || $parts === []) {
            $parts[] = $diff->d . ' ' . ($diff->d === 1 ? 'dia' : 'dias');
        }

        return implode(', ', array_slice($parts, 0, 3));
    }
}
