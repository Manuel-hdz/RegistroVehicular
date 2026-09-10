<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! \App\Support\MigrationSchemaInspector::hasColumn('warehouse_entry_materials', 'quantity')) {
            Schema::table('warehouse_entry_materials', function (Blueprint $table) {
                $table->decimal('quantity', 12, 2)->default(1)->after('part_id');
            });
        }

        if (! \App\Support\MigrationSchemaInspector::hasColumn('warehouse_material_exits', 'quantity')) {
            Schema::table('warehouse_material_exits', function (Blueprint $table) {
                $table->decimal('quantity', 12, 2)->default(1)->after('part_id');
            });
        }
    }

    public function down(): void
    {
        Schema::table('warehouse_material_exits', function (Blueprint $table) {
            $table->dropColumn('quantity');
        });

        Schema::table('warehouse_entry_materials', function (Blueprint $table) {
            $table->dropColumn('quantity');
        });
    }
};
