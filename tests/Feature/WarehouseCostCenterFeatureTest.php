<?php

namespace Tests\Feature;

use App\Models\CostCenter;
use App\Models\Part;
use App\Models\User;
use App\Models\WarehouseEntry;
use App\Models\WarehouseLocation;
use App\Models\WarehouseMaterialExit;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WarehouseCostCenterFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_inventory_only_shows_stock_from_selected_cost_center(): void
    {
        $user = $this->warehouseUser();
        $charcas = CostCenter::where('code', 'ZARPEO-CHARCAS')->firstOrFail();
        $matriz = CostCenter::where('code', 'INDIRECTOS-MATRIZ')->firstOrFail();
        $user->costCenters()->sync([$charcas->id, $matriz->id]);

        $charcasPart = Part::create([
            'clave' => 'MAT-CHARCAS',
            'name' => 'Material Charcas',
            'unit_cost' => 0,
            'active' => true,
        ]);
        $matrizPart = Part::create([
            'clave' => 'MAT-MATRIZ',
            'name' => 'Material Matriz',
            'unit_cost' => 0,
            'active' => true,
        ]);

        $this->entry($charcas, $charcasPart, 5);
        $this->entry($matriz, $matrizPart, 9);

        $this->actingAs($user)
            ->get(route('warehouse.inventory', ['cost_center_id' => $charcas->id]))
            ->assertOk()
            ->assertSee('Zarpeo Charcas')
            ->assertSee('Material Charcas')
            ->assertSee('5.00')
            ->assertDontSee('Material Matriz');
    }

    public function test_exit_cannot_use_stock_from_another_cost_center(): void
    {
        $user = $this->warehouseUser();
        $charcas = CostCenter::where('code', 'ZARPEO-CHARCAS')->firstOrFail();
        $matriz = CostCenter::where('code', 'INDIRECTOS-MATRIZ')->firstOrFail();
        $user->costCenters()->sync([$charcas->id, $matriz->id]);
        $part = Part::create([
            'clave' => 'MAT-COMPARTIDO',
            'name' => 'Material compartido',
            'unit_cost' => 0,
            'active' => true,
        ]);

        $this->entry($charcas, $part, 2);
        $this->entry($matriz, $part, 10);

        $this->actingAs($user)
            ->from(route('warehouse.movements', ['cost_center_id' => $charcas->id]))
            ->post(route('warehouse.material-exits.store'), [
                'cost_center_id' => $charcas->id,
                'part_id' => $part->id,
                'quantity' => 3,
            ])
            ->assertSessionHasErrors('quantity');

        $this->assertDatabaseMissing('warehouse_material_exits', [
            'cost_center_id' => $charcas->id,
            'part_id' => $part->id,
        ]);

        $this->actingAs($user)
            ->post(route('warehouse.material-exits.store'), [
                'cost_center_id' => $matriz->id,
                'part_id' => $part->id,
                'quantity' => 3,
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('warehouse_material_exits', [
            'cost_center_id' => $matriz->id,
            'part_id' => $part->id,
            'quantity' => 3,
            'registered_by_user_id' => $user->id,
            'registered_by_username' => $user->username,
        ]);
    }

    public function test_user_cannot_open_an_unassigned_cost_center(): void
    {
        $user = $this->warehouseUser();
        $charcas = CostCenter::where('code', 'ZARPEO-CHARCAS')->firstOrFail();
        $matriz = CostCenter::where('code', 'INDIRECTOS-MATRIZ')->firstOrFail();
        $user->costCenters()->sync([$charcas->id]);

        $this->actingAs($user)
            ->get(route('warehouse.inventory', ['cost_center_id' => $matriz->id]))
            ->assertForbidden();
    }

    public function test_repeated_material_lines_are_grouped_by_name_and_characteristics(): void
    {
        $user = $this->warehouseUser();
        $charcas = CostCenter::where('code', 'ZARPEO-CHARCAS')->firstOrFail();
        $user->costCenters()->sync([$charcas->id]);
        $location = $this->location($charcas);
        $part = Part::create([
            'clave' => 'CLAVE-ANTERIOR',
            'name' => 'Filtro de aceite',
            'characteristics' => ['color negro', 'motor diesel'],
            'unit_cost' => 0,
            'active' => true,
        ]);
        $this->entry($charcas, $part, 4);

        $this->actingAs($user)->post(route('warehouse.materials.store'), [
            'cost_center_id' => $charcas->id,
            'entry_type' => 'Compra',
            'entry_date' => now()->format('Y-m-d H:i:s'),
            'materials' => [
                [
                    'part_id' => $part->id,
                    'warehouse_location_id' => $location->id,
                    'name' => 'Filtro de aceite',
                    'characteristics' => "color negro\nmotor diesel",
                    'quantity' => 2,
                ],
                [
                    'name' => '  FILTRO   DE ACEITE ',
                    'warehouse_location_id' => $location->id,
                    'characteristics' => 'motor diesel, color negro',
                    'quantity' => 3,
                ],
            ],
        ])->assertRedirect(route('warehouse.movements', ['cost_center_id' => $charcas->id]));

        $newEntry = WarehouseEntry::latest('id')->firstOrFail();
        $this->assertSame($user->id, $newEntry->registered_by_user_id);
        $this->assertSame($user->username, $newEntry->registered_by_username);
        $this->assertDatabaseCount('parts', 1);
        $this->assertCount(1, $newEntry->materials);
        $this->assertSame($part->id, $newEntry->materials->first()->part_id);
        $this->assertSame(5.0, (float) $newEntry->materials->first()->quantity);
        $this->assertSame(9.0, (float) $part->entryMaterials()->sum('quantity'));
    }

    public function test_same_name_with_different_characteristics_creates_another_material(): void
    {
        $user = $this->warehouseUser();
        $charcas = CostCenter::where('code', 'ZARPEO-CHARCAS')->firstOrFail();
        $user->costCenters()->sync([$charcas->id]);
        $location = $this->location($charcas);

        foreach (['color negro', 'color blanco'] as $characteristics) {
            $this->actingAs($user)->post(route('warehouse.materials.store'), [
                'cost_center_id' => $charcas->id,
                'entry_type' => 'Compra',
                'entry_date' => now()->format('Y-m-d H:i:s'),
                'materials' => [[
                    'name' => 'Cable eléctrico',
                    'warehouse_location_id' => $location->id,
                    'characteristics' => $characteristics,
                    'quantity' => 1,
                ]],
            ])->assertRedirect();
        }

        $this->assertDatabaseCount('parts', 2);
        $this->assertEqualsCanonicalizing(
            [['color negro'], ['color blanco']],
            Part::all()->pluck('characteristics')->all()
        );
    }

    public function test_entry_search_only_exposes_materials_from_the_selected_cost_center(): void
    {
        $user = $this->warehouseUser();
        $charcas = CostCenter::where('code', 'ZARPEO-CHARCAS')->firstOrFail();
        $matriz = CostCenter::where('code', 'INDIRECTOS-MATRIZ')->firstOrFail();
        $user->costCenters()->sync([$charcas->id, $matriz->id]);
        $charcasPart = Part::create([
            'clave' => 'BUSQUEDA-CHARCAS',
            'name' => 'Material sugerido Charcas',
            'characteristics' => ['acero', 'grande'],
            'unit_cost' => 0,
            'active' => true,
        ]);
        $matrizPart = Part::create([
            'clave' => 'BUSQUEDA-MATRIZ',
            'name' => 'Material privado Matriz',
            'characteristics' => ['plastico'],
            'unit_cost' => 0,
            'active' => true,
        ]);
        $this->entry($charcas, $charcasPart, 2);
        $this->entry($matriz, $matrizPart, 2);

        $this->actingAs($user)
            ->get(route('warehouse.movements', ['cost_center_id' => $charcas->id]))
            ->assertOk()
            ->assertSee('Material sugerido Charcas')
            ->assertSee('acero')
            ->assertDontSee('Material privado Matriz');
    }

    public function test_latest_movement_lists_only_show_clickable_ids_linked_to_complete_tables(): void
    {
        $user = $this->warehouseUser();
        $charcas = CostCenter::where('code', 'ZARPEO-CHARCAS')->firstOrFail();
        $user->costCenters()->sync([$charcas->id]);
        $part = Part::create([
            'clave' => 'DETALLE-ID',
            'name' => 'Manguera industrial',
            'characteristics' => ['resistente al calor', '20 metros'],
            'unit_cost' => 0,
            'active' => true,
        ]);
        $entry = $this->entry($charcas, $part, 8);
        $exit = WarehouseMaterialExit::create([
            'cost_center_id' => $charcas->id,
            'registered_by_user_id' => $user->id,
            'registered_by_username' => $user->username,
            'part_id' => $part->id,
            'quantity' => 2,
            'exit_date' => now(),
            'source' => 'Almacén',
            'destination' => 'Obra',
            'responsible' => 'Responsable',
            'voucher_number' => 'VALE-DETALLE-ID',
            'status' => 'entregado',
        ]);

        $response = $this->actingAs($user)->get(route('warehouse.movements', [
            'cost_center_id' => $charcas->id,
        ]));

        $response->assertOk()
            ->assertSee('data-record-row="entry-record-'.$entry->id.'"', false)
            ->assertSee('data-record-row="exit-record-'.$exit->id.'"', false)
            ->assertSee('id="entry-record-'.$entry->id.'"', false)
            ->assertSee('id="exit-record-'.$exit->id.'"', false)
            ->assertSee('resistente al calor')
            ->assertSee('20 metros');

        $html = $response->getContent();
        $this->assertSame(1, preg_match('/<h3>Ultimas 10 entradas<\/h3>\s*<ul[^>]*>(.*?)<\/ul>/s', $html, $entryList));
        $this->assertSame(1, preg_match('/<h3>Ultimas 10 salidas<\/h3>\s*<ul[^>]*>(.*?)<\/ul>/s', $html, $exitList));
        $this->assertSame($entry->entry_key, preg_replace('/\s+/', ' ', trim(strip_tags($entryList[1]))));
        $this->assertSame($exit->voucher_number, preg_replace('/\s+/', ' ', trim(strip_tags($exitList[1]))));
    }

    private function warehouseUser(): User
    {
        return User::create([
            'name' => 'Usuario almacén',
            'username' => 'warehouse-cost-centers',
            'password' => 'secret',
            'role' => 'user',
            'department' => 'almacen',
            'active' => true,
        ]);
    }

    private function entry(CostCenter $costCenter, Part $part, float $quantity): WarehouseEntry
    {
        $entry = WarehouseEntry::create([
            'cost_center_id' => $costCenter->id,
            'entry_key' => 'ENT-'.$costCenter->id.'-'.$part->id,
            'entry_type' => 'Compra',
            'entry_date' => now(),
        ]);
        $entry->materials()->create([
            'part_id' => $part->id,
            'quantity' => $quantity,
        ]);

        return $entry;
    }

    private function location(CostCenter $costCenter): WarehouseLocation
    {
        return $costCenter->warehouseLocations()->where('normalized_name', 'general')->firstOrFail();
    }
}
