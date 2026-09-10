<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServicioRegistro extends Model
{
    protected $fillable = [
        'empleado_id',
        'medidor',
        'tipo_servicio',
        'medicion',
        'fecha_servicio',
        'mecanico',
        'lugar',
        'supervisor',
        'mensaje',
    ];

    protected $casts = [
        'fecha_servicio' => 'date',
    ];

    public function empleado(): BelongsTo
    {
        return $this->belongsTo(Empleado::class);
    }
}