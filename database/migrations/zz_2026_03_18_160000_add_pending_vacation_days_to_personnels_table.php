<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('personnels')) {
            return;
        }

        Schema::table('personnels', function (Blueprint $table) {
            if (!$this->hasColumn('personnels', 'pending_vacation_days')) {
                $table->unsignedInteger('pending_vacation_days')->default(0)->after('terminated_at');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('personnels')) {
            return;
        }

        Schema::table('personnels', function (Blueprint $table) {
            if ($this->hasColumn('personnels', 'pending_vacation_days')) {
                $table->dropColumn('pending_vacation_days');
            }
        });
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
