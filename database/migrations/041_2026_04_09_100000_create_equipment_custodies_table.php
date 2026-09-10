<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('equipment_custodies')) {
            return;
        }

        Schema::create('equipment_custodies', function (Blueprint $table) {
            $table->id();
            $table->string('equipment_type', 30)->index();
            $table->string('brand', 120);
            $table->string('model', 120);
            $table->string('serial_number', 120);
            $table->text('accessories')->nullable();
            $table->date('assigned_at');
            $table->string('responsible', 180);
            $table->foreignId('registered_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('registered_by_username', 100);
            $table->string('notification_email', 190)->nullable();
            $table->timestamps();

            $table->index(['equipment_type', 'assigned_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment_custodies');
    }
};
