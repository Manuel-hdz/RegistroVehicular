<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $driver = Schema::getConnection()->getDriverName();
        if ($driver === 'sqlite') {
            return;
        }

        if (!Schema::hasTable('movements') || !Schema::hasTable('vehicles') || !$this->hasColumn('movements', 'vehicle_id')) {
            return;
        }

        $foreignKeys = $this->movementVehicleForeignKeys();
        $alreadyReferencesVehicles = collect($foreignKeys)->contains(
            fn ($foreignKey) => ($foreignKey->referenced_table ?? null) === 'vehicles'
        );

        if ($alreadyReferencesVehicles) {
            return;
        }

        foreach ($foreignKeys as $foreignKey) {
            $constraint = (string) ($foreignKey->constraint_name ?? '');
            if ($constraint !== '') {
                $escapedConstraint = str_replace('`', '``', $constraint);
                DB::statement("ALTER TABLE `movements` DROP FOREIGN KEY `{$escapedConstraint}`");
            }
        }

        $invalidVehicleReferences = DB::table('movements as m')
            ->leftJoin('vehicles as v', 'v.id', '=', 'm.vehicle_id')
            ->whereNull('v.id')
            ->count();

        if ($invalidVehicleReferences > 0) {
            return;
        }

        if (!$this->hasIndex('movements', 'movements_vehicle_id_index')) {
            DB::statement('ALTER TABLE `movements` ADD INDEX `movements_vehicle_id_index` (`vehicle_id`)');
        }

        DB::statement('ALTER TABLE `movements` ADD CONSTRAINT `movements_vehicle_id_foreign` FOREIGN KEY (`vehicle_id`) REFERENCES `vehicles` (`id`)');
    }

    public function down(): void
    {
        // No revertimos a una referencia incorrecta (vehicles1).
    }

    private function movementVehicleForeignKeys(): array
    {
        return DB::select(
            <<<'SQL'
            SELECT
                kcu.CONSTRAINT_NAME AS constraint_name,
                kcu.REFERENCED_TABLE_NAME AS referenced_table
            FROM information_schema.KEY_COLUMN_USAGE kcu
            WHERE kcu.TABLE_SCHEMA = DATABASE()
              AND kcu.TABLE_NAME = 'movements'
              AND kcu.COLUMN_NAME = 'vehicle_id'
              AND kcu.REFERENCED_TABLE_NAME IS NOT NULL
            SQL
        );
    }

    private function hasColumn(string $table, string $column): bool
    {
        $result = DB::selectOne(
            'SELECT COUNT(*) AS total FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?',
            [$table, $column]
        );

        return (int) ($result->total ?? 0) > 0;
    }

    private function hasIndex(string $table, string $indexName): bool
    {
        $result = DB::selectOne(
            'SELECT COUNT(*) AS total FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND INDEX_NAME = ?',
            [$table, $indexName]
        );

        return (int) ($result->total ?? 0) > 0;
    }
};
