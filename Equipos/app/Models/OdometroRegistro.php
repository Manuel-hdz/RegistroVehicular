<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OdometroRegistro extends Model
{
    protected $fillable = [
        'empleado_id',
        'kilometros',
        'registrado_en',
        'nota',
    ];

    protected $casts = [
        'kilometros' => 'decimal:2',
        'registrado_en' => 'date',
    ];

    public function empleado(): BelongsTo
    {
        return $this->belongsTo(Empleado::class);
    }
}