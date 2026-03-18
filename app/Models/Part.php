<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Part extends Model
{
    use HasFactory;

    protected $fillable = ['name','unit_cost','active'];

    protected $casts = [
        'unit_cost' => 'decimal:2',
        'active' => 'boolean',
    ];

    public function repairs(): BelongsToMany
    {
        return $this->belongsToMany(Repair::class)->withPivot('quantity')->withTimestamps();
    }
}
