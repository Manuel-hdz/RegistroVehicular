<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('unidades')) {
            return;
        }

        $this->copyIfMissing('placas', 'placa');
        $this->copyIfMissing('numero_serie_eq_adicional', 'numero_serie_adicional');
        $this->copyIfMissing('tipo_motor', 'motor');
        $this->copyIfMissing('asignado_a', 'personal_asignado');
        $this->copyIfMissing('estado', 'estatus_operativo');
        $this->copyIfMissing('refacciones', 'ultimo_comentario_mantenimiento');

        Schema::table('unidades', function (Blueprint $table) {
            foreach ($this->legacyColumns() as $column) {
                if (Schema::hasColumn('unidades', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('unidades')) {
            return;
        }

        Schema::table('unidades', function (Blueprint $table) {
            if (! Schema::hasColumn('unidades', 'placa')) {
                $table->string('placa', 50)->nullable();
            }

            if (! Schema::hasColumn('unidades', 'tipo')) {
                $table->string('tipo', 100)->nullable();
            }

            if (! Schema::hasColumn('unidades', 'anio')) {
                $table->unsignedSmallInteger('anio')->nullable();
            }

            if (! Schema::hasColumn('unidades', 'numero_serie_adicional')) {
                $table->string('numero_serie_adicional', 100)->nullable();
            }

            if (! Schema::hasColumn('unidades', 'motor')) {
                $table->string('motor', 100)->nullable();
            }

            if (! Schema::hasColumn('unidades', 'personal_asignado')) {
                $table->string('personal_asignado')->nullable();
            }

            if (! Schema::hasColumn('unidades', 'estatus_operativo')) {
                $table->string('estatus_operativo', 100)->nullable();
            }

            if (! Schema::hasColumn('unidades', 'ultimo_comentario_mantenimiento')) {
                $table->text('ultimo_comentario_mantenimiento')->nullable();
            }

            if (! Schema::hasColumn('unidades', 'poliza_seguro')) {
                $table->string('poliza_seguro')->nullable();
            }

            if (! Schema::hasColumn('unidades', 'activo')) {
                $table->boolean('activo')->default(true);
            }

            if (! Schema::hasColumn('unidades', 'disponible')) {
                $table->boolean('disponible')->default(false);
            }
        });
    }

    private function copyIfMissing(string $target, string $source): void
    {
        if (! Schema::hasColumn('unidades', $target) || ! Schema::hasColumn('unidades', $source)) {
            return;
        }

        DB::table('unidades')
            ->where(function ($query) use ($target) {
                $query->whereNull($target)->orWhere($target, '');
            })
            ->whereNotNull($source)
            ->where($source, '!=', '')
            ->update([$target => DB::raw($source)]);
    }

    private function legacyColumns(): array
    {
        return [
            'placa',
            'tipo',
            'anio',
            'numero_serie_adicional',
            'motor',
            'personal_asignado',
            'estatus_operativo',
            'ultimo_comentario_mantenimiento',
            'poliza_seguro',
            'activo',
            'disponible',
        ];
    }
};
