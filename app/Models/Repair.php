<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Repair extends Model
{
    use HasFactory;

    protected $fillable = ['vehicle_id','started_at','duration_hours','notes'];

    protected $casts = [
        'started_at' => 'datetime',
        'duration_hours' => 'decimal:2',
    ];

    public function vehicle(): BelongsTo { return $this->belongsTo(Vehicle::class); }
    public function parts(): BelongsToMany { return $this->belongsToMany(Part::class)->withPivot('quantity')->withTimestamps(); }
    public function mechanics(): BelongsToMany { return $this->belongsToMany(Mechanic::class)->withPivot('hours')->withTimestamps(); }

    public function partsCost(): float
    {
        return (float) $this->parts->sum(fn($p) => $p->pivot->quantity * (float) $p->unit_cost);
    }

    public function laborCost(): float
    {
        return (float) $this->mechanics->sum(fn($m) => ((float)$m->daily_salary / 8.0) * (float)$m->pivot->hours);
    }

    public function totalCost(): float
    {
        return $this->partsCost() + $this->laborCost();
    }
}

