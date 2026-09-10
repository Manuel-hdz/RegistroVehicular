<?php

namespace Tests\Feature;

use App\Models\CostCenter;
use App\Models\User;
use App\Models\WarehouseEntry;
use App\Models\WarehouseLocation;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WarehouseLocationFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_warehouse_administrator_can_create_and_update_locations(): void
    {
        $admin = $this->user('admin', 'almacen', 'warehouse-location-admin');
        $costCenter = CostCenter::where('code', 'ZARPEO-CHARCAS')->firstOrFail();
        $admin->costCenters()->sync([$costCenter->id]);

        $this->actingAs($admin)
            ->get(route('warehouse.locations.index', ['cost_center_id' => $costCenter->id]))
            ->assertOk()
            ->assertSee('Ubicaciones de almacén');

        $this->actingAs($admin)->post(route('warehouse.locations.store'), [
            'cost_center_id' => $costCenter->id,
            'name' => 'Estante A-01',
            'description' => 'Zona de filtros',
        ])->assertRedirect(route('warehouse.locations.index', ['cost_center_id' => $costCenter->id]));

        $location = WarehouseLocation::where('normalized_name', 'estante a-01')->firstOrFail();
        $this->assertSame($admin->id, $location->created_by);
        $this->assertSame($admin->id, $location->updated_by);

        $this->actingAs($admin)->patch(route('warehouse.locations.update', $location), [
            'name' => 'Estante A-02',
            'description' => 'Zona de filtros actualizada',
            'active' => '1',
        ])->assertRedirect();

        $this->assertDatabaseHas('warehouse_locations', [
            'id' => $location->id,
            'name' => 'Estante A-02',
            'normalized_name' => 'estante a-02',
            'updated_by' => $admin->id,
        ]);
    }

    public function test_regular_warehouse_user_cannot_manage_locations(): void
    {
        $user = $this->user('user', 'almacen', 'warehouse-location-user');
        $costCenter = CostCenter::where('code', 'ZARPEO-CHARCAS')->firstOrFail();
        $user->costCenters()->sync([$costCenter->id]);

        $this->actingAs($user)
            ->get(route('warehouse.locations.index', ['cost_center_id' => $costCenter->id]))
            ->assertForbidden();
        $this->actingAs($user)->post(route('warehouse.locations.store'), [
            'cost_center_id' => $costCenter->id,
            'name' => 'No autorizada',
        ])->assertForbidden();
    }

    public function test_entry_records_location_per_material_and_rejects_another_cost_centers_location(): void
    {
        $user = $this->user('user', 'almacen', 'warehouse-entry-location');
        $charcas = CostCenter::where('code', 'ZARPEO-CHARCAS')->firstOrFail();
        $matriz = CostCenter::where('code', 'INDIRECTOS-MATRIZ')->firstOrFail();
        $user->costCenters()->sync([$charcas->id, $matriz->id]);
        $charcasGeneral = $charcas->warehouseLocations()->where('normalized_name', 'general')->firstOrFail();
        $charcasShelf = $charcas->warehouseLocations()->create([
            'name' => 'Estante B-01',
            'active' => true,
        ]);
        $matrizGeneral = $matriz->warehouseLocations()->where('normalized_name', 'general')->firstOrFail();

        $this->actingAs($user)->post(route('warehouse.materials.store'), [
            'cost_center_id' => $charcas->id,
            'entry_type' => 'Compra',
            'entry_date' => now()->format('Y-m-d H:i:s'),
            'materials' => [
                [
                    'name' => 'Tornillo hexagonal',
                    'characteristics' => 'acero',
                    'warehouse_location_id' => $charcasGeneral->id,
                    'quantity' => 2,
                ],
                [
                    'name' => 'Tornillo hexagonal',
                    'characteristics' => 'acero',
                    'warehouse_location_id' => $charcasShelf->id,
                    'quantity' => 3,
                ],
            ],
        ])->assertRedirect();

        $entry = WarehouseEntry::latest('id')->firstOrFail();
        $this->assertCount(2, $entry->materials);
        $this->assertEqualsCanonicalizing(
            [$charcasGeneral->id, $charcasShelf->id],
            $entry->materials->pluck('warehouse_location_id')->all()
        );
        $this->actingAs($user)
            ->get(route('warehouse.inventory', ['cost_center_id' => $charcas->id]))
            ->assertOk()
            ->assertSee('General')
            ->assertSee('Estante B-01');

        $entryCount = WarehouseEntry::count();
        $this->actingAs($user)
            ->from(route('warehouse.movements', ['cost_center_id' => $charcas->id]))
            ->post(route('warehouse.materials.store'), [
                'cost_center_id' => $charcas->id,
                'entry_type' => 'Compra',
                'entry_date' => now()->format('Y-m-d H:i:s'),
                'materials' => [[
                    'name' => 'Material inválido',
                    'warehouse_location_id' => $matrizGeneral->id,
                    'quantity' => 1,
                ]],
            ])
            ->assertSessionHasErrors('materials');
        $this->assertSame($entryCount, WarehouseEntry::count());
    }

    private function user(string $role, string $department, string $username): User
    {
        return User::create([
            'name' => $username,
            'username' => $username,
            'password' => 'secret',
            'role' => $role,
            'department' => $department,
            'active' => true,
        ]);
    }
}
