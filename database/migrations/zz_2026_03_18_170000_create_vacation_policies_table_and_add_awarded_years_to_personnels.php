<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('vacation_policies')) {
            Schema::create('vacation_policies', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('service_year')->unique();
                $table->unsignedInteger('vacation_days');
                $table->string('notes', 255)->nullable();
                $table->boolean('active')->default(true);
                $table->timestamps();
            });
        }

        if (Schema::hasTable('personnels')) {
            Schema::table('personnels', function (Blueprint $table) {
                if (!$this->hasColumn('personnels', 'vacation_years_awarded')) {
                    $table->unsignedInteger('vacation_years_awarded')->default(0)->after('pending_vacation_days');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('personnels')) {
            Schema::table('personnels', function (Blueprint $table) {
                if ($this->hasColumn('personnels', 'vacation_years_awarded')) {
                    $table->dropColumn('vacation_years_awarded');
                }
            });
        }

        Schema::dropIfExists('vacation_policies');
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
