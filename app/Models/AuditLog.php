<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditLog extends Model
{
    public const ACTIONS = [
        'created' => 'Alta', 'updated' => 'Cambio', 'deleted' => 'Baja',
        'database.insert' => 'Alta masiva / relación', 'database.update' => 'Cambio masivo',
        'database.delete' => 'Baja masiva / relación', 'request' => 'Solicitud / consulta',
        'login' => 'Inicio de sesión', 'logout' => 'Cierre de sesión',
        'login_failed' => 'Acceso rechazado', 'log' => 'Log del sistema',
    ];

    public $timestamps = false;

    protected $guarded = [];

    protected $casts = [
        'before_values' => 'array', 'after_values' => 'array',
        'metadata' => 'array', 'created_at' => 'datetime',
    ];
}
