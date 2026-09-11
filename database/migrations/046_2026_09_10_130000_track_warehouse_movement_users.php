<?php

use App\Support\MigrationSchemaInspector;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! MigrationSchemaInspector::hasColumn('warehouse_entries', 'registered_by_user_id')) {
            Schema::table('warehouse_entries', function (Blueprint $table) {
                $table->foreignId('registered_by_user_id')
                    ->nullable()
                    ->after('cost_center_id')
                    ->constrained('users')
                    ->nullOnDelete();
            });
        }

        if (! MigrationSchemaInspector::hasColumn('warehouse_entries', 'registered_by_username')) {
            Schema::table('warehouse_entries', function (Blueprint $table) {
                $table->string('registered_by_username', 100)
                    ->nullable()
                    ->after('registered_by_user_id');
            });
        }

        if (! MigrationSchemaInspector::hasColumn('warehouse_material_exits', 'registered_by_user_id')) {
            Schema::table('warehouse_material_exits', function (Blueprint $table) {
                $table->foreignId('registered_by_user_id')
                    ->nullable()
                    ->after('cost_center_id')
                    ->constrained('users')
                    ->nullOnDelete();
            });
        }

        if (! MigrationSchemaInspector::hasColumn('warehouse_material_exits', 'registered_by_username')) {
            Schema::table('warehouse_material_exits', function (Blueprint $table) {
                $table->string('registered_by_username', 100)
                    ->nullable()
                    ->after('registered_by_user_id');
            });
        }

        DB::table('warehouse_entries')
            ->whereNull('registered_by_username')
            ->update(['registered_by_username' => 'Registro previo']);
        DB::table('warehouse_material_exits')
            ->whereNull('registered_by_username')
            ->update(['registered_by_username' => 'Registro previo']);
    }

    public function down(): void
    {
        if (MigrationSchemaInspector::hasColumn('warehouse_material_exits', 'registered_by_user_id')) {
            Schema::table('warehouse_material_exits', function (Blueprint $table) {
                $table->dropConstrainedForeignId('registered_by_user_id');
            });
        }

        if (MigrationSchemaInspector::hasColumn('warehouse_material_exits', 'registered_by_username')) {
            Schema::table('warehouse_material_exits', function (Blueprint $table) {
                $table->dropColumn('registered_by_username');
            });
        }

        if (MigrationSchemaInspector::hasColumn('warehouse_entries', 'registered_by_user_id')) {
            Schema::table('warehouse_entries', function (Blueprint $table) {
                $table->dropConstrainedForeignId('registered_by_user_id');
            });
        }

        if (MigrationSchemaInspector::hasColumn('warehouse_entries', 'registered_by_username')) {
            Schema::table('warehouse_entries', function (Blueprint $table) {
                $table->dropColumn('registered_by_username');
            });
        }
    }
};
