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
        if (! Schema::hasTable('warehouse_locations')) {
            Schema::create('warehouse_locations', function (Blueprint $table) {
                $table->id();
                $table->foreignId('cost_center_id')->constrained('cost_centers')->cascadeOnDelete();
                $table->string('name', 120);
                $table->string('normalized_name', 120);
                $table->string('description', 255)->nullable();
                $table->boolean('active')->default(true);
                $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
                $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
                $table->timestamps();
                $table->unique(['cost_center_id', 'normalized_name']);
            });
        }

        if (! MigrationSchemaInspector::hasColumn('warehouse_entry_materials', 'warehouse_location_id')) {
            Schema::table('warehouse_entry_materials', function (Blueprint $table) {
                $table->foreignId('warehouse_location_id')
                    ->nullable()
                    ->after('part_id')
                    ->constrained('warehouse_locations')
                    ->restrictOnDelete();
            });
        }

        $now = now();
        $defaultLocationIds = [];

        foreach (DB::table('cost_centers')->get(['id']) as $costCenter) {
            DB::table('warehouse_locations')->updateOrInsert(
                ['cost_center_id' => $costCenter->id, 'normalized_name' => 'general'],
                [
                    'name' => 'General',
                    'description' => 'Ubicación predeterminada',
                    'active' => true,
                    'updated_at' => $now,
                    'created_at' => $now,
                ]
            );

            $defaultLocationIds[(int) $costCenter->id] = (int) DB::table('warehouse_locations')
                ->where('cost_center_id', $costCenter->id)
                ->where('normalized_name', 'general')
                ->value('id');
        }

        DB::table('warehouse_entries')
            ->orderBy('id')
            ->select(['id', 'cost_center_id'])
            ->chunkById(100, function ($entries) use ($defaultLocationIds): void {
                foreach ($entries as $entry) {
                    $locationId = $defaultLocationIds[(int) $entry->cost_center_id] ?? null;
                    if (! $locationId) {
                        continue;
                    }

                    DB::table('warehouse_entry_materials')
                        ->where('warehouse_entry_id', $entry->id)
                        ->whereNull('warehouse_location_id')
                        ->update(['warehouse_location_id' => $locationId]);
                }
            });
    }

    public function down(): void
    {
        if (MigrationSchemaInspector::hasColumn('warehouse_entry_materials', 'warehouse_location_id')) {
            Schema::table('warehouse_entry_materials', function (Blueprint $table) {
                $table->dropConstrainedForeignId('warehouse_location_id');
            });
        }

        Schema::dropIfExists('warehouse_locations');
    }
};
