<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('odometro_registros', function (Blueprint $table) {
            $table->id();
            $table->foreignId('empleado_id')->constrained('unidades')->cascadeOnDelete();
            $table->decimal('kilometros', 10, 2);
            $table->date('registrado_en')->nullable();
            $table->string('nota')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('odometro_registros');
    }
};