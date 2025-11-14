<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->string('vtype')->nullable()->after('identifier'); // auto, pickup, camion
            $table->string('availability')->default('available')->after('active'); // available|unavailable
            $table->text('maintenance_note')->nullable()->after('availability');
        });
    }

    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn(['vtype','availability','maintenance_note']);
        });
    }
};

