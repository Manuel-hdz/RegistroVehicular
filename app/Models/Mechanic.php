<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Mechanic extends Model
{
    use HasFactory;

    protected $fillable = ['personnel_id', 'name', 'daily_salary', 'active'];

    protected $casts = [
        'active' => 'boolean',
    ];

    public function repairs(): BelongsToMany
    {
        return $this->belongsToMany(Repair::class)->withPivot('hours')->withTimestamps();
    }

    public function personnel(): BelongsTo
    {
        return $this->belongsTo(Personnel::class);
    }
}
