<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Mechanic extends Model
{
    use HasFactory;

    protected $fillable = ['name','daily_salary','active'];

    public function repairs(): BelongsToMany
    {
        return $this->belongsToMany(Repair::class)->withPivot('hours')->withTimestamps();
    }
}

