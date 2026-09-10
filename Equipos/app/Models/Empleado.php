<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Empleado extends Model
{
    protected $table = 'unidades';

    // Campos del vehiculo que Laravel permite guardar desde formularios o API.
    protected $fillable = [
        'clave',
        'nombre_equipo',
        'fecha_alta',
        'marca_modelo',
        'modelo',
        'poliza',
        'numero_serie',
        'numero_serie_eq_adicional',
        'placas',
        'tenencia',
        'tarjeta_circulacion',
        'tipo_motor',
        'area',
        'familia',
        'fecha_fabricacion',
        'asignado_a',
        'estado',
        'proveedor',
        'descripcion',
        'horometro_odometro',
        'horometro_horas',
        'horometro_en_marcha',
        'horometro_iniciado_en',
        'disponibilidad',
        'refacciones',
        'factura',
        'tipo_filtro',
        'foto_path',
        'poliza_pdf_path',
        'factura_pdf_path',
        'es_easter_egg',
        'easter_egg_editado',
    ];

    // Convierte fechas y estados booleanos al usar el modelo.
    protected $casts = [
        'fecha_alta' => 'date',
        'fecha_fabricacion' => 'date',
        'horometro_horas' => 'decimal:2',
        'horometro_en_marcha' => 'boolean',
        'horometro_iniciado_en' => 'datetime',
        'es_easter_egg' => 'boolean',
        'easter_egg_editado' => 'boolean',
    ];

    public const HOROMETRO_LIMITE_CICLO = 1000;

    public const ODOMETRO_LIMITE_CICLO = 5000;

    public const ODOMETRO_ALERTAS = [
        2200 => 'Medio servicio.',
        4700 => 'Gama completa.',
    ];

    public const HOROMETRO_ALERTAS = [
        75 => 'Medio servicio de cambio de filtro de admicion.',
        200 => 'Hacer gama completa.',
        325 => 'Medio servicio de cambio de filtro de admicion.',
        450 => 'Hacer gama completa.',
        575 => 'Medio servicio de cambio de filtro de admicion.',
        700 => 'Hacer gama completa.',
        825 => 'Medio servicio de cambio de filtro de admicion.',
        950 => 'Hacer gama completa.',
    ];

    public const EASTER_EGG_BARCODE = '75007614';

    public const EASTER_EGG_CLAVE = 'COCA-COLA-75007614';

    public function usaHorometro(): bool
    {
        return $this->horometro_odometro === 'Horometro';
    }

    public function usaOdometro(): bool
    {
        return $this->horometro_odometro === 'Odometro';
    }

    public function odometroRegistros(): HasMany
    {
        return $this->hasMany(OdometroRegistro::class);
    }

    public function servicioRegistros(): HasMany
    {
        return $this->hasMany(ServicioRegistro::class);
    }

    public function odometroKilometrosTotales(): float
    {
        if ($this->relationLoaded('odometroRegistros')) {
            return round((float) $this->odometroRegistros->sum('kilometros'), 2);
        }

        return round((float) $this->odometroRegistros()->sum('kilometros'), 2);
    }

    public function odometroKilometrosCiclo(): float
    {
        return $this->normalizarKilometrosCiclo($this->odometroKilometrosTotales());
    }

    public function alertaOdometroActual(): ?array
    {
        $kilometros = $this->odometroKilometrosCiclo();
        $alerta = null;

        foreach (self::ODOMETRO_ALERTAS as $limite => $mensaje) {
            if ($kilometros >= $limite) {
                $alerta = [
                    'kilometros' => $limite,
                    'mensaje' => $mensaje,
                ];
            }
        }

        return $alerta;
    }

    public function proximaAlertaOdometro(): ?array
    {
        $kilometros = $this->odometroKilometrosCiclo();

        foreach (self::ODOMETRO_ALERTAS as $limite => $mensaje) {
            if ($kilometros < $limite) {
                return [
                    'kilometros' => $limite,
                    'faltan' => round($limite - $kilometros, 2),
                    'mensaje' => $mensaje,
                ];
            }
        }

        return null;
    }

    public function normalizarKilometrosCiclo(float $kilometros): float
    {
        return round(max(0, $kilometros), 2);
    }

    public function horometroHorasActuales(): float
    {
        $horas = (float) ($this->horometro_horas ?? 0);

        if ($this->horometro_en_marcha && $this->horometro_iniciado_en) {
            $horas += $this->horometro_iniciado_en->diffInSeconds(now()) / 3600;
        }

        return $this->normalizarHorasCiclo($horas);
    }

    public function alertaHorometroActual(): ?array
    {
        $horas = $this->horometroHorasActuales();
        $alerta = null;

        foreach (self::HOROMETRO_ALERTAS as $limite => $mensaje) {
            if ($horas >= $limite) {
                $alerta = [
                    'horas' => $limite,
                    'mensaje' => $mensaje,
                ];
            }
        }

        return $alerta;
    }

    public function proximaAlertaHorometro(): ?array
    {
        $horas = $this->horometroHorasActuales();

        foreach (self::HOROMETRO_ALERTAS as $limite => $mensaje) {
            if ($horas < $limite) {
                return [
                    'horas' => $limite,
                    'faltan' => round($limite - $horas, 2),
                    'mensaje' => $mensaje,
                ];
            }
        }

        return [
            'horas' => self::HOROMETRO_LIMITE_CICLO,
            'faltan' => round(self::HOROMETRO_LIMITE_CICLO - $horas, 2),
            'mensaje' => 'Reiniciar ciclo del horometro.',
        ];
    }

    public function normalizarHorasCiclo(float $horas): float
    {
        $horas = max(0, $horas);

        if ($horas >= self::HOROMETRO_LIMITE_CICLO) {
            $horas = fmod($horas, self::HOROMETRO_LIMITE_CICLO);
        }

        return round($horas, 2);
    }
    // Texto que se guarda dentro del QR: abre el detalle del vehiculo con su clave.
    public function qrContenido(): string
    {
        return route('empleados.show', [
            'empleado' => $this->clave,
        ]);
    }

    // Imagen QR generada automaticamente desde el contenido del vehiculo.
    public function qrImagenUrl(int $size = 160): string
    {
        $fontSize = max(10, (int) round($size * 0.09));

        return 'https://quickchart.io/qr?' . http_build_query([
            'text' => $this->qrContenido(),
            'size' => $size,
            'margin' => 2,
            'caption' => $this->clave,
            'captionFontFamily' => 'mono',
            'captionFontSize' => $fontSize,
        ]);
    }

    public function tienePolizaPdf(): bool
    {
        return filled($this->poliza_pdf_path) && Storage::disk('local')->exists($this->poliza_pdf_path);
    }

    public function tieneFacturaPdf(): bool
    {
        return filled($this->factura_pdf_path) && Storage::disk('local')->exists($this->factura_pdf_path);
    }

    public function tieneFoto(): bool
    {
        return filled($this->foto_path) && Storage::disk('local')->exists($this->foto_path);
    }
}

