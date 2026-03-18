<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('drivers') && !$this->hasColumn('drivers', 'personnel_id')) {
            Schema::table('drivers', function (Blueprint $table) {
                $table->foreignId('personnel_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('personnels')
                    ->nullOnDelete();
            });
        }

        if (Schema::hasTable('mechanics') && !$this->hasColumn('mechanics', 'personnel_id')) {
            Schema::table('mechanics', function (Blueprint $table) {
                $table->foreignId('personnel_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('personnels')
                    ->nullOnDelete();
            });
        }

        $personnelRows = DB::table('personnels')
            ->select('id', 'employee_number', 'first_name', 'last_name', 'middle_name')
            ->get();

        $personnelByEmployee = [];
        $personnelByName = [];

        foreach ($personnelRows as $personnel) {
            $fullName = $this->normalizeName(implode(' ', array_filter([
                $personnel->first_name,
                $personnel->last_name,
                $personnel->middle_name,
            ])));

            if (!empty($personnel->employee_number)) {
                $personnelByEmployee[$personnel->employee_number] = $personnel->id;
            }

            if ($fullName !== '') {
                $personnelByName[$fullName] = $personnel->id;
            }
        }

        if (Schema::hasTable('drivers') && $this->hasColumn('drivers', 'personnel_id')) {
            $drivers = DB::table('drivers')
                ->select('id', 'employee_number', 'name', 'personnel_id')
                ->whereNull('personnel_id')
                ->get();

            foreach ($drivers as $driver) {
                $personnelId = $personnelByEmployee[$driver->employee_number] ?? null;

                if (!$personnelId) {
                    $personnelId = $personnelByName[$this->normalizeName((string) $driver->name)] ?? null;
                }

                if ($personnelId) {
                    DB::table('drivers')
                        ->where('id', $driver->id)
                        ->update(['personnel_id' => $personnelId]);
                }
            }
        }

        if (Schema::hasTable('mechanics') && $this->hasColumn('mechanics', 'personnel_id')) {
            $mechanics = DB::table('mechanics')
                ->select('id', 'name', 'personnel_id')
                ->whereNull('personnel_id')
                ->get();

            foreach ($mechanics as $mechanic) {
                $personnelId = $personnelByName[$this->normalizeName((string) $mechanic->name)] ?? null;

                if ($personnelId) {
                    DB::table('mechanics')
                        ->where('id', $mechanic->id)
                        ->update(['personnel_id' => $personnelId]);
                }
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('mechanics') && $this->hasColumn('mechanics', 'personnel_id')) {
            Schema::table('mechanics', function (Blueprint $table) {
                $table->dropConstrainedForeignId('personnel_id');
            });
        }

        if (Schema::hasTable('drivers') && $this->hasColumn('drivers', 'personnel_id')) {
            Schema::table('drivers', function (Blueprint $table) {
                $table->dropConstrainedForeignId('personnel_id');
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

    private function normalizeName(string $value): string
    {
        return trim(preg_replace('/\s+/', ' ', mb_strtolower($value)) ?? '');
    }
};
