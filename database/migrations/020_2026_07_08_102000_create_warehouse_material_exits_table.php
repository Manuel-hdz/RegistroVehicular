<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('warehouse_material_exits')) {
            return;
        }

        Schema::create('warehouse_material_exits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('part_id')->constrained('parts');
            $table->dateTime('exit_date');
            $table->string('source', 180);
            $table->string('destination', 180);
            $table->string('responsible', 180);
            $table->string('voucher_number')->nullable()->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('warehouse_material_exits');
    }
};
