<?php

use App\Support\MigrationSchemaInspector;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const OLD_UNIQUE = 'warehouse_locations_cost_center_id_normalized_name_unique';

    private const LEVEL_UNIQUE = 'warehouse_locations_center_name_level_unique';

    private const COST_CENTER_INDEX = 'warehouse_locations_cost_center_id_index';

    public function up(): void
    {
        if (! Schema::hasTable('warehouse_locations')) {
            return;
        }

        if (! MigrationSchemaInspector::hasColumn('warehouse_locations', 'level')) {
            Schema::table('warehouse_locations', function (Blueprint $table) {
                $table->unsignedTinyInteger('level')->default(1)->after('normalized_name');
            });
        }

        if (! MigrationSchemaInspector::hasIndex('warehouse_locations', self::COST_CENTER_INDEX)) {
            Schema::table('warehouse_locations', function (Blueprint $table) {
                $table->index('cost_center_id', self::COST_CENTER_INDEX);
            });
        }

        if (MigrationSchemaInspector::hasIndex('warehouse_locations', self::OLD_UNIQUE)) {
            Schema::table('warehouse_locations', function (Blueprint $table) {
                $table->dropUnique(self::OLD_UNIQUE);
            });
        }

        if (! MigrationSchemaInspector::hasIndex('warehouse_locations', self::LEVEL_UNIQUE)) {
            Schema::table('warehouse_locations', function (Blueprint $table) {
                $table->unique(['cost_center_id', 'normalized_name', 'level'], self::LEVEL_UNIQUE);
            });
        }
    }

    public function down(): void
    {
        if (! Schema::hasTable('warehouse_locations')) {
            return;
        }

        if (MigrationSchemaInspector::hasIndex('warehouse_locations', self::LEVEL_UNIQUE)) {
            Schema::table('warehouse_locations', function (Blueprint $table) {
                $table->dropUnique(self::LEVEL_UNIQUE);
            });
        }

        if (MigrationSchemaInspector::hasColumn('warehouse_locations', 'level')) {
            Schema::table('warehouse_locations', function (Blueprint $table) {
                $table->dropColumn('level');
            });
        }

        if (! MigrationSchemaInspector::hasIndex('warehouse_locations', self::OLD_UNIQUE)) {
            Schema::table('warehouse_locations', function (Blueprint $table) {
                $table->unique(['cost_center_id', 'normalized_name'], self::OLD_UNIQUE);
            });
        }

        if (MigrationSchemaInspector::hasIndex('warehouse_locations', self::COST_CENTER_INDEX)) {
            Schema::table('warehouse_locations', function (Blueprint $table) {
                $table->dropIndex(self::COST_CENTER_INDEX);
            });
        }
    }
};
