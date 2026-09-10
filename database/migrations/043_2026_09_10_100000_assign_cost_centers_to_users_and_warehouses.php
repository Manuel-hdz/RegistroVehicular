<?php

use App\Support\MigrationSchemaInspector;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const DEFAULT_CODE = 'INDIRECTOS-MATRIZ';

    private const COST_CENTERS = [
        'ZARPEO-CHARCAS' => 'Zarpeo Charcas',
        'ZARPEO-SAN-MARTIN' => 'Zarpeo San Martin',
        'ZARPEO-ASIENTOS' => 'Zarpeo Asientos',
        self::DEFAULT_CODE => 'Indirectos Matriz',
        'CONTRAPOZOS-BACIS' => 'Contrapozos Bacis',
        'CONTRAPOZOS-CHARCAS' => 'Contrapozos Charcas',
    ];

    public function up(): void
    {
        $now = now();

        foreach (self::COST_CENTERS as $code => $name) {
            DB::table('cost_centers')->updateOrInsert(
                ['code' => $code],
                ['name' => $name, 'active' => true, 'updated_at' => $now, 'created_at' => $now]
            );
        }

        if (! Schema::hasTable('cost_center_user')) {
            Schema::create('cost_center_user', function (Blueprint $table) {
                $table->id();
                $table->foreignId('cost_center_id')->constrained('cost_centers')->cascadeOnDelete();
                $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
                $table->timestamps();
                $table->unique(['cost_center_id', 'user_id']);
            });
        }

        if (! MigrationSchemaInspector::hasColumn('warehouse_entries', 'cost_center_id')) {
            Schema::table('warehouse_entries', function (Blueprint $table) {
                $table->foreignId('cost_center_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('cost_centers')
                    ->restrictOnDelete();
            });
        }

        if (! MigrationSchemaInspector::hasColumn('warehouse_material_exits', 'cost_center_id')) {
            Schema::table('warehouse_material_exits', function (Blueprint $table) {
                $table->foreignId('cost_center_id')
                    ->nullable()
                    ->after('id')
                    ->constrained('cost_centers')
                    ->restrictOnDelete();
            });
        }

        $defaultCostCenterId = DB::table('cost_centers')
            ->where('code', self::DEFAULT_CODE)
            ->value('id');

        if ($defaultCostCenterId) {
            DB::table('warehouse_entries')
                ->whereNull('cost_center_id')
                ->update(['cost_center_id' => $defaultCostCenterId]);

            DB::table('warehouse_material_exits')
                ->whereNull('cost_center_id')
                ->update(['cost_center_id' => $defaultCostCenterId]);

            $userRows = DB::table('users')->pluck('id')->map(fn ($userId) => [
                'cost_center_id' => $defaultCostCenterId,
                'user_id' => $userId,
                'created_at' => $now,
                'updated_at' => $now,
            ])->all();

            if ($userRows !== []) {
                DB::table('cost_center_user')->insertOrIgnore($userRows);
            }
        }
    }

    public function down(): void
    {
        if (MigrationSchemaInspector::hasColumn('warehouse_material_exits', 'cost_center_id')) {
            Schema::table('warehouse_material_exits', function (Blueprint $table) {
                $table->dropConstrainedForeignId('cost_center_id');
            });
        }

        if (MigrationSchemaInspector::hasColumn('warehouse_entries', 'cost_center_id')) {
            Schema::table('warehouse_entries', function (Blueprint $table) {
                $table->dropConstrainedForeignId('cost_center_id');
            });
        }

        Schema::dropIfExists('cost_center_user');
    }
};
