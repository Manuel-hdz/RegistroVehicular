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
            // Agrega los nuevos parametros del vehiculo sin rehacer la tabla existente.
            if (! Schema::hasColumn('empleados', 'placa')) {
                $table->string('placa', 50)->nullable()->after('id');
            }

            if (! Schema::hasColumn('empleados', 'tipo')) {
                $table->string('tipo', 100)->nullable()->after('placa');
            }

            if (! Schema::hasColumn('empleados', 'modelo')) {
                $table->string('modelo', 100)->nullable()->after('tipo');
            }

            if (! Schema::hasColumn('empleados', 'anio')) {
                $table->unsignedSmallInteger('anio')->nullable()->after('modelo');
            }

            if (! Schema::hasColumn('empleados', 'numero_serie')) {
                $table->string('numero_serie', 100)->nullable()->after('anio');
            }

            if (! Schema::hasColumn('empleados', 'numero_serie_adicional')) {
                $table->string('numero_serie_adicional', 100)->nullable()->after('numero_serie');
            }

            if (! Schema::hasColumn('empleados', 'motor')) {
                $table->string('motor', 100)->nullable()->after('numero_serie_adicional');
            }

            if (! Schema::hasColumn('empleados', 'proveedor')) {
                $table->string('proveedor')->nullable()->after('motor');
            }

            if (! Schema::hasColumn('empleados', 'personal_asignado')) {
                $table->string('personal_asignado')->nullable()->after('proveedor');
            }

            if (! Schema::hasColumn('empleados', 'estatus_operativo')) {
                $table->string('estatus_operativo', 100)->nullable()->after('personal_asignado');
            }

            if (! Schema::hasColumn('empleados', 'ultimo_comentario_mantenimiento')) {
                $table->text('ultimo_comentario_mantenimiento')->nullable()->after('estatus_operativo');
            }

            if (! Schema::hasColumn('empleados', 'descripcion')) {
                $table->text('descripcion')->nullable()->after('ultimo_comentario_mantenimiento');
            }

            if (! Schema::hasColumn('empleados', 'tarjeta_circulacion')) {
                $table->string('tarjeta_circulacion')->nullable()->after('descripcion');
            }

            if (! Schema::hasColumn('empleados', 'poliza_seguro')) {
                $table->string('poliza_seguro')->nullable()->after('tarjeta_circulacion');
            }

            foreach (['nombre', 'puesto', 'correo', 'telefono', 'activo'] as $column) {
                // Quita columnas antiguas del CRUD de empleados si todavia existen.
                if (Schema::hasColumn('empleados', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('empleados', function (Blueprint $table) {
            foreach ([
                // Al revertir, elimina los campos nuevos de vehiculos.
                'placa',
                'tipo',
                'modelo',
                'anio',
                'numero_serie',
                'numero_serie_adicional',
                'motor',
                'proveedor',
                'personal_asignado',
                'estatus_operativo',
                'ultimo_comentario_mantenimiento',
                'descripcion',
                'tarjeta_circulacion',
                'poliza_seguro',
            ] as $column) {
                if (Schema::hasColumn('empleados', $column)) {
                    $table->dropColumn($column);
                }
            }

            if (! Schema::hasColumn('empleados', 'nombre')) {
                // Restaura las columnas antiguas para dejar reversible la migracion.
                $table->string('nombre')->nullable()->after('id');
            }

            if (! Schema::hasColumn('empleados', 'puesto')) {
                $table->string('puesto')->nullable()->after('nombre');
            }

            if (! Schema::hasColumn('empleados', 'correo')) {
                $table->string('correo')->nullable()->after('puesto');
            }

            if (! Schema::hasColumn('empleados', 'telefono')) {
                $table->string('telefono')->nullable()->after('correo');
            }

            if (! Schema::hasColumn('empleados', 'activo')) {
                $table->boolean('activo')->default(true)->after('telefono');
            }
        });
    }
};
