<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('cost_centers')) {
            Schema::create('cost_centers', function (Blueprint $table) {
                $table->id();
                $table->string('code', 50)->unique();
                $table->string('name', 150);
                $table->boolean('active')->default(true);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('requisitions')) {
            Schema::create('requisitions', function (Blueprint $table) {
                $table->id();
                $table->foreignId('cost_center_id')->constrained('cost_centers');
                $table->string('requester_name', 150);
                $table->foreignId('vehicle_id')->nullable()->constrained('vehicles')->nullOnDelete();
                $table->string('status', 30)->default('pending');
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('requisition_items')) {
            Schema::create('requisition_items', function (Blueprint $table) {
                $table->id();
                $table->foreignId('requisition_id')->constrained('requisitions')->cascadeOnDelete();
                $table->string('material_name', 180);
                $table->decimal('quantity', 10, 2)->default(1);
                $table->foreignId('equipment_vehicle_id')->nullable()->constrained('vehicles')->nullOnDelete();
                $table->string('justification', 255)->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('requisition_items');
        Schema::dropIfExists('requisitions');
        Schema::dropIfExists('cost_centers');
    }
};
