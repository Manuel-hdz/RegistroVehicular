<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('requisition_items') && !$this->hasColumn('requisition_items', 'is_ordered')) {
            Schema::table('requisition_items', function (Blueprint $table) {
                $table->boolean('is_ordered')->default(false)->after('equipment_vehicle_id');
            });
        }

        if (Schema::hasTable('requisition_items') && !$this->hasColumn('requisition_items', 'is_in_storage')) {
            Schema::table('requisition_items', function (Blueprint $table) {
                $table->boolean('is_in_storage')->default(false)->after('is_ordered');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('requisition_items') && $this->hasColumn('requisition_items', 'is_in_storage')) {
            Schema::table('requisition_items', function (Blueprint $table) {
                $table->dropColumn('is_in_storage');
            });
        }

        if (Schema::hasTable('requisition_items') && $this->hasColumn('requisition_items', 'is_ordered')) {
            Schema::table('requisition_items', function (Blueprint $table) {
                $table->dropColumn('is_ordered');
            });
        }
    }

    private function hasColumn(string $table, string $column): bool
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            $columns = DB::select("PRAGMA table_info('{$table}')");

            return collect($columns)->contains(fn ($item) => ($item->name ?? null) === $column);
        }

        $result = DB::selectOne(
            'SELECT COUNT(*) AS total FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?',
            [$table, $column]
        );

        return (int) ($result->total ?? 0) > 0;
    }
};
