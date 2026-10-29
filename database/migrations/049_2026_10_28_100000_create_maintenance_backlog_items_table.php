<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('maintenance_backlog_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('vehicle_id')->constrained()->restrictOnDelete();
            $table->foreignId('cost_center_id')->constrained()->restrictOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->decimal('quantity', 12, 2);
            $table->string('unit', 50);
            $table->string('description', 1000);
            $table->decimal('unit_cost', 12, 2);
            $table->decimal('total_cost', 18, 2);
            $table->string('system_type', 50);
            $table->string('criticality', 10);
            $table->string('status', 20);
            $table->timestamps();
            $table->index(['cost_center_id', 'vehicle_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('maintenance_backlog_items');
    }
};
