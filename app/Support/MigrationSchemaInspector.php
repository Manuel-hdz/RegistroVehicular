<?php

namespace App\Support;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

final class MigrationSchemaInspector
{
    public static function hasColumn(string $table, string $column): bool
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            $escapedTable = str_replace("'", "''", $table);
            $columns = DB::select("PRAGMA table_info('{$escapedTable}')");

            return collect($columns)->contains(
                fn ($item) => ($item->name ?? null) === $column
            );
        }

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            $result = DB::selectOne(
                'SELECT COUNT(*) AS total FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ?',
                [$table, $column]
            );

            return (int) ($result->total ?? 0) > 0;
        }

        return Schema::hasColumn($table, $column);
    }

    public static function hasIndex(string $table, string $index): bool
    {
        $driver = Schema::getConnection()->getDriverName();

        if ($driver === 'sqlite') {
            $escapedTable = str_replace("'", "''", $table);
            $indexes = DB::select("PRAGMA index_list('{$escapedTable}')");

            return collect($indexes)->contains(
                fn ($item) => ($item->name ?? null) === $index
            );
        }

        if (in_array($driver, ['mysql', 'mariadb'], true)) {
            $result = DB::selectOne(
                'SELECT COUNT(*) AS total FROM information_schema.STATISTICS WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND INDEX_NAME = ?',
                [$table, $index]
            );

            return (int) ($result->total ?? 0) > 0;
        }

        return Schema::hasIndex($table, $index);
    }
}
