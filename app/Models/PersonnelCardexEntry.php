<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PersonnelCardexEntry extends Model
{
    use HasFactory;

    protected $fillable = [
        'personnel_id',
        'entry_date',
        'code',
        'notes',
        'updated_by',
    ];

    protected $casts = [
        'entry_date' => 'date',
    ];

    public function personnel(): BelongsTo
    {
        return $this->belongsTo(Personnel::class);
    }

    public function updatedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'updated_by');
    }
}
