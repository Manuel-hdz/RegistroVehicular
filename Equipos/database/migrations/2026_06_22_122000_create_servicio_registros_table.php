<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('servicio_registros', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empleado_id')->constrained('unidades')->cascadeOnDelete();
            $table->string('medidor', 30);
            $table->string('tipo_servicio', 100);
            $table->string('medicion', 100);
            $table->date('fecha_servicio');
            $table->string('mecanico');
            $table->string('lugar');
            $table->string('supervisor');
            $table->string('mensaje')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('servicio_registros');
    }
};