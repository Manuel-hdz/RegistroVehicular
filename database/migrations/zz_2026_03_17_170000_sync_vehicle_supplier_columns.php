<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $hasSupplier = $this->columnExists('supplier');
        $hasAssignedPersonnel = $this->columnExists('assigned_personnel');

        Schema::table('vehicles', function (Blueprint $table) use ($hasSupplier, $hasAssignedPersonnel) {
            if (!$hasSupplier) {
                $table->string('supplier', 150)->nullable()->after('engine_number');
            }

            if (!$hasAssignedPersonnel) {
                $table->string('assigned_personnel', 150)->nullable()->after('supplier');
            }
        });

        if ($this->columnExists('assigned_provider') && $this->columnExists('supplier')) {
            DB::statement("
                UPDATE vehicles
                SET supplier = assigned_provider
                WHERE supplier IS NULL
                  AND assigned_provider IS NOT NULL
                  AND assigned_provider <> ''
            ");
        }
    }

    public function down(): void
    {
        $hasAssignedPersonnel = $this->columnExists('assigned_personnel');
        $hasSupplier = $this->columnExists('supplier');

        Schema::table('vehicles', function (Blueprint $table) use ($hasAssignedPersonnel, $hasSupplier) {
            if ($hasAssignedPersonnel) {
                $table->dropColumn('assigned_personnel');
            }

            if ($hasSupplier) {
                $table->dropColumn('supplier');
            }
        });
    }

    private function columnExists(string $column): bool
    {
        if (DB::getDriverName() === 'sqlite') {
            $rows = DB::select("PRAGMA table_info('vehicles')");

            foreach ($rows as $row) {
                if (($row->name ?? null) === $column) {
                    return true;
                }
            }

            return false;
        }

        $rows = DB::select(
            'SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?',
            ['vehicles', $column]
        );

        return count($rows) > 0;
    }
};
