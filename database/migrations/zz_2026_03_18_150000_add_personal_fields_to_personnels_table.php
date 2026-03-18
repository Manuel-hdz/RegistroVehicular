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
            if (!$this->hasColumn('personnels', 'marital_status')) {
                $table->string('marital_status', 50)->nullable()->after('nss');
            }

            if (!$this->hasColumn('personnels', 'sex')) {
                $table->string('sex', 20)->nullable()->after('marital_status');
            }

            if (!$this->hasColumn('personnels', 'birth_date')) {
                $table->date('birth_date')->nullable()->after('sex');
            }

            if (!$this->hasColumn('personnels', 'account_number')) {
                $table->string('account_number', 50)->nullable()->after('hire_date');
            }

            if (!$this->hasColumn('personnels', 'account_type')) {
                $table->string('account_type', 50)->nullable()->after('account_number');
            }
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('personnels')) {
            return;
        }

        Schema::table('personnels', function (Blueprint $table) {
            $columns = collect(['account_type', 'account_number', 'birth_date', 'sex', 'marital_status'])
                ->filter(fn ($column) => $this->hasColumn('personnels', $column))
                ->all();

            if ($columns !== []) {
                $table->dropColumn($columns);
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
