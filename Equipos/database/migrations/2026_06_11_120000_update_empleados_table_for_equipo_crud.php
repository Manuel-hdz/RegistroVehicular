<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('empleados', function (Blueprint $table) {
            // Agrega los parametros nuevos del CRUD de equipos.
            $this->addString($table, 'clave', 100, 'id');
            $this->addString($table, 'nombre_equipo', 255, 'clave');
            $this->addDate($table, 'fecha_alta', 'nombre_equipo');
            $this->addString($table, 'marca_modelo', 255, 'fecha_alta');
            $this->addString($table, 'poliza', 255, 'modelo');
            $this->addString($table, 'numero_serie_eq_adicional', 100, 'numero_serie');
            $this->addString($table, 'placas', 50, 'numero_serie_eq_adicional');
            $this->addString($table, 'tenencia', 255, 'placas');
            $this->addString($table, 'tipo_motor', 100, 'tarjeta_circulacion');
            $this->addString($table, 'area', 150, 'tipo_motor');
            $this->addString($table, 'familia', 150, 'area');
            $this->addDate($table, 'fecha_fabricacion', 'familia');
            $this->addString($table, 'asignado_a', 255, 'fecha_fabricacion');
            $this->addString($table, 'estado', 100, 'asignado_a');
            $this->addString($table, 'horometro_odometro', 100, 'descripcion');
            $this->addString($table, 'disponibilidad', 100, 'horometro_odometro');
            $this->addText($table, 'refacciones', 'disponibilidad');
            $this->addString($table, 'factura', 255, 'refacciones');
            $this->addString($table, 'tipo_filtro', 150, 'factura');
            $this->addString($table, 'poliza_pdf_path', 255, 'tipo_filtro');
            $this->addString($table, 'factura_pdf_path', 255, 'poliza_pdf_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('empleados', function (Blueprint $table) {
            foreach ([
                'factura_pdf_path',
                'poliza_pdf_path',
                'tipo_filtro',
                'factura',
                'refacciones',
                'disponibilidad',
                'horometro_odometro',
                'estado',
                'asignado_a',
                'fecha_fabricacion',
                'familia',
                'area',
                'tipo_motor',
                'tenencia',
                'placas',
                'numero_serie_eq_adicional',
                'poliza',
                'marca_modelo',
                'fecha_alta',
                'nombre_equipo',
                'clave',
            ] as $column) {
                if (Schema::hasColumn('empleados', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    private function addString(Blueprint $table, string $column, int $length, string $after): void
    {
        if (! Schema::hasColumn('empleados', $column)) {
            $definition = $table->string($column, $length)->nullable();

            if (Schema::hasColumn('empleados', $after)) {
                $definition->after($after);
            }
        }
    }

    private function addDate(Blueprint $table, string $column, string $after): void
    {
        if (! Schema::hasColumn('empleados', $column)) {
            $definition = $table->date($column)->nullable();

            if (Schema::hasColumn('empleados', $after)) {
                $definition->after($after);
            }
        }
    }

    private function addText(Blueprint $table, string $column, string $after): void
    {
        if (! Schema::hasColumn('empleados', $column)) {
            $definition = $table->text($column)->nullable();

            if (Schema::hasColumn('empleados', $after)) {
                $definition->after($after);
            }
        }
    }
};
