<?php

namespace Tests\Feature;

use App\Models\CostCenter;
use App\Models\Part;
use App\Models\User;
use App\Models\WarehouseEntry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WarehouseInventorySearchFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_inventory_can_be_searched_by_material_data(): void
    {
        $user = User::create([
            'name' => 'Almacen',
            'username' => 'almacen-search',
            'password' => 'secret',
            'role' => 'user',
            'department' => 'almacen',
            'active' => true,
        ]);

        $costCenter = CostCenter::where('code', 'INDIRECTOS-MATRIZ')->firstOrFail();
        $filter = Part::create(['clave' => 'MAT-FILTRO-001', 'name' => 'Filtro de aceite', 'characteristics' => ['motor diesel', 'color negro'], 'unit_cost' => 0, 'active' => true]);
        Part::create(['clave' => 'MAT-BUJIA-002', 'name' => 'Bujia', 'characteristics' => ['iridio'], 'unit_cost' => 0, 'active' => true]);
        $entry = WarehouseEntry::create([
            'cost_center_id' => $costCenter->id,
            'entry_key' => 'ENT-SEARCH-001',
            'entry_type' => 'Compra',
            'entry_date' => now(),
        ]);
        $entry->materials()->create(['part_id' => $filter->id, 'quantity' => 4]);

        $this->actingAs($user)
            ->get(route('warehouse.inventory', ['search' => 'diesel']))
            ->assertOk()
            ->assertSee('Filtro de aceite')
            ->assertDontSee('Bujia')
            ->assertSee('value="diesel"', false);
    }
}
