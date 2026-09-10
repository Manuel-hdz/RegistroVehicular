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
            // Estados mutuamente excluyentes para el vehiculo.
            if (! Schema::hasColumn('empleados', 'activo')) {
                $table->boolean('activo')->default(true)->after('poliza_seguro');
            }

            if (! Schema::hasColumn('empleados', 'disponible')) {
                $table->boolean('disponible')->default(false)->after('activo');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('empleados', function (Blueprint $table) {
            // El orden evita problemas al quitar primero la columna agregada al final.
            if (Schema::hasColumn('empleados', 'disponible')) {
                $table->dropColumn('disponible');
            }

            if (Schema::hasColumn('empleados', 'activo')) {
                $table->dropColumn('activo');
            }
        });
    }
};
