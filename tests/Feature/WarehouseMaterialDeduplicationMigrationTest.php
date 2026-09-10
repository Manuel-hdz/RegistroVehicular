<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class WarehouseMaterialDeduplicationMigrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_migration_merges_existing_duplicate_materials_and_their_entry_quantities(): void
    {
        $migration = require database_path('migrations/044_2026_09_10_110000_consolidate_duplicate_warehouse_materials.php');
        $migration->down();

        $firstPartId = DB::table('parts')->insertGetId([
            'clave' => 'CLAVE-ANTERIOR-1',
            'name' => 'Filtro de aceite',
            'characteristics' => json_encode(['color negro', 'motor diésel']),
            'unit_cost' => 0,
            'active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $duplicatePartId = DB::table('parts')->insertGetId([
            'clave' => 'CLAVE-ANTERIOR-2',
            'name' => '  FILTRO   DE ACEITE ',
            'characteristics' => json_encode(['motor diesel', 'color negro']),
            'unit_cost' => 0,
            'active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $entryId = DB::table('warehouse_entries')->insertGetId([
            'cost_center_id' => DB::table('cost_centers')->where('code', 'INDIRECTOS-MATRIZ')->value('id'),
            'entry_key' => 'ENT-DUPLICADOS',
            'entry_type' => 'Compra',
            'entry_date' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        DB::table('warehouse_entry_materials')->insert([
            [
                'warehouse_entry_id' => $entryId,
                'part_id' => $firstPartId,
                'quantity' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'warehouse_entry_id' => $entryId,
                'part_id' => $duplicatePartId,
                'quantity' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $migration->up();

        $this->assertDatabaseCount('parts', 1);
        $this->assertDatabaseHas('parts', [
            'id' => $firstPartId,
        ]);
        $this->assertDatabaseMissing('parts', [
            'id' => $duplicatePartId,
        ]);
        $this->assertDatabaseCount('warehouse_entry_materials', 1);
        $this->assertDatabaseHas('warehouse_entry_materials', [
            'warehouse_entry_id' => $entryId,
            'part_id' => $firstPartId,
            'quantity' => 5,
        ]);
        $this->assertNotNull(DB::table('parts')->where('id', $firstPartId)->value('identity_key'));
    }
}
