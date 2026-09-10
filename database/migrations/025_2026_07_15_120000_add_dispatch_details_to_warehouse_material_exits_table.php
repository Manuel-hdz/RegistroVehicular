<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! \App\Support\MigrationSchemaInspector::hasColumn('warehouse_material_exits', 'dispatched_by')) {
            Schema::table('warehouse_material_exits', function (Blueprint $table) {
                $table->string('dispatched_by', 180)->nullable()->after('source');
            });
        }

        if (! \App\Support\MigrationSchemaInspector::hasColumn('warehouse_material_exits', 'carried_by')) {
            Schema::table('warehouse_material_exits', function (Blueprint $table) {
                $table->string('carried_by', 180)->nullable()->after('dispatched_by');
            });
        }

        if (! \App\Support\MigrationSchemaInspector::hasColumn('warehouse_material_exits', 'invoice_path')) {
            Schema::table('warehouse_material_exits', function (Blueprint $table) {
                $table->string('invoice_path')->nullable()->after('responsible');
            });
        }
    }

    public function down(): void
    {
        Schema::table('warehouse_material_exits', function (Blueprint $table) {
            $table->dropColumn(['dispatched_by', 'carried_by', 'invoice_path']);
        });
    }
};
