<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained('vehicles');
            $table->foreignId('driver_id')->constrained('drivers');
            $table->foreignId('guard_out_id')->nullable()->constrained('users');
            $table->foreignId('guard_in_id')->nullable()->constrained('users');

            $table->integer('odometer_out');
            $table->unsignedTinyInteger('fuel_out');
            $table->dateTime('departed_at');
            $table->string('destination')->nullable();
            $table->text('notes_out')->nullable();

            $table->integer('odometer_in')->nullable();
            $table->unsignedTinyInteger('fuel_in')->nullable();
            $table->dateTime('arrived_at')->nullable();
            $table->text('notes_in')->nullable();

            $table->string('status')->default('open');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('movements');
    }
};

