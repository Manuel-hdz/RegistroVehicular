<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Vehicle extends Model
{
    use HasFactory;

    protected $fillable = [
        'plate',
        'identifier',
        'unit_name',
        'registration_date',
        'make_model',
        'vtype',
        'model',
        'year',
        'active',
        'availability',
        'maintenance_note',
        'serial_number',
        'additional_serial_number',
        'engine_number',
        'engine_type',
        'engine_filters',
        'area',
        'family',
        'manufacture_date',
        'equipment_status',
        'supplier',
        'assigned_personnel',
        'description',
        'photo_path',
        'circulation_card_path',
        'tenure_path',
        'insurance_policy_path',
    ];

    protected $casts = [
        'active' => 'boolean',
        'year' => 'integer',
        'registration_date' => 'date',
        'manufacture_date' => 'date',
    ];

    public function movements(): HasMany
    {
        return $this->hasMany(Movement::class);
    }

    public function getCirculationCardUrlAttribute(): ?string
    {
        if (!$this->circulation_card_path) {
            return null;
        }

        return route('vehicles.document', ['vehicle' => $this, 'document' => 'circulation-card']);
    }

    public function getTenureUrlAttribute(): ?string
    {
        if (!$this->tenure_path) {
            return null;
        }

        return route('vehicles.document', ['vehicle' => $this, 'document' => 'tenure']);
    }

    public function getInsurancePolicyUrlAttribute(): ?string
    {
        if (!$this->insurance_policy_path) {
            return null;
        }

        return route('vehicles.document', ['vehicle' => $this, 'document' => 'insurance-policy']);
    }

    public function getPhotoUrlAttribute(): ?string
    {
        if (!$this->photo_path) {
            return null;
        }

        return route('vehicles.document', ['vehicle' => $this, 'document' => 'photo']);
    }
}
