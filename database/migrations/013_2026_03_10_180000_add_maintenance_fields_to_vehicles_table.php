<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! \App\Support\MigrationSchemaInspector::hasColumn('vehicles', 'vtype')) {
            Schema::table('vehicles', function (Blueprint $table) {
                $table->string('vtype')->nullable()->after('identifier'); // auto, pickup, camion
            });
        }

        if (! \App\Support\MigrationSchemaInspector::hasColumn('vehicles', 'availability')) {
            Schema::table('vehicles', function (Blueprint $table) {
                $table->string('availability')->default('available')->after('active'); // available|unavailable
            });
        }

        if (! \App\Support\MigrationSchemaInspector::hasColumn('vehicles', 'maintenance_note')) {
            Schema::table('vehicles', function (Blueprint $table) {
                $table->text('maintenance_note')->nullable()->after('availability');
            });
        }
    }

    public function down(): void
    {
        Schema::table('vehicles', function (Blueprint $table) {
            $table->dropColumn(['vtype', 'availability', 'maintenance_note']);
        });
    }
};
