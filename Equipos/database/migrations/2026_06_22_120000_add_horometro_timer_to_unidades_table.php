<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('unidades', function (Blueprint $table) {
            if (! Schema::hasColumn('unidades', 'horometro_horas')) {
                $table->decimal('horometro_horas', 8, 2)->default(0)->after('horometro_odometro');
            }

            if (! Schema::hasColumn('unidades', 'horometro_en_marcha')) {
                $table->boolean('horometro_en_marcha')->default(false)->after('horometro_horas');
            }

            if (! Schema::hasColumn('unidades', 'horometro_iniciado_en')) {
                $table->timestamp('horometro_iniciado_en')->nullable()->after('horometro_en_marcha');
            }
        });
    }

    public function down(): void
    {
        Schema::table('unidades', function (Blueprint $table) {
            foreach (['horometro_iniciado_en', 'horometro_en_marcha', 'horometro_horas'] as $column) {
                if (Schema::hasColumn('unidades', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};