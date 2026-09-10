<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('comedor_records')) {
            return;
        }

        Schema::create('comedor_records', function (Blueprint $table) {
            $table->id();
            $table->string('name', 120);
            $table->timestamp('recorded_at');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comedor_records');
    }
};
