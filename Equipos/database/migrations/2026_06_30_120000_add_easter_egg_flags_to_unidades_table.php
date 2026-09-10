<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('unidades')) {
            return;
        }

        Schema::table('unidades', function (Blueprint $table) {
            if (! Schema::hasColumn('unidades', 'es_easter_egg')) {
                $table->boolean('es_easter_egg')->default(false)->after('factura_pdf_path');
            }

            if (! Schema::hasColumn('unidades', 'easter_egg_editado')) {
                $table->boolean('easter_egg_editado')->default(false)->after('es_easter_egg');
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('unidades')) {
            return;
        }

        Schema::table('unidades', function (Blueprint $table) {
            foreach (['easter_egg_editado', 'es_easter_egg'] as $column) {
                if (Schema::hasColumn('unidades', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
