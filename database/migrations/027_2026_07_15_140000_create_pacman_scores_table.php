<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('pacman_scores')) {
            return;
        }

        Schema::create('pacman_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('score');
            $table->string('difficulty', 20);
            $table->timestamps();
            $table->index(['score', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pacman_scores');
    }
};
