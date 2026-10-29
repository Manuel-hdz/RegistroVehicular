<?php

namespace Tests\Feature;

use App\Models\CostCenter;
use App\Models\Part;
use App\Models\User;
use App\Models\WarehouseEntry;
use App\Models\WarehouseEntryMaterial;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WarehouseLocationPermissionFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_superadmin_can_grant_and_revoke_location_permission_without_changing_the_user_role(): void
    {
        $superadmin = User::factory()->create(['role' => 'superadmin', 'department' => 'sistemas', 'active' => true]);
        $user = $this->warehouseUser();
        $center = $user->costCenters()->firstOrFail();

        $this->actingAs($superadmin)->get(route('users.index', ['selected_user' => $user->id]))
            ->assertOk()->assertSee('Cambiar ubicaciones de materiales');
        $this->get(route('users.create'))->assertOk()->assertSee('ubicaciones_almacen');
        $this->patch(route('users.permissions', $user), ['module_permissions' => ['ubicaciones_almacen']])
            ->assertSessionHasNoErrors()->assertRedirect();
        $this->assertTrue($user->fresh()->canManageWarehouseLocations());
        $this->assertSame('user', $user->fresh()->role);

        $this->actingAs($user->fresh())->get(route('warehouse.locations.index', ['cost_center_id' => $center->id]))->assertOk();
        $this->post(route('warehouse.locations.store'), ['cost_center_id' => $center->id, 'name' => 'Rack autorizado', 'level' => 1])
            ->assertSessionHasNoErrors()->assertRedirect();
        $location = $center->warehouseLocations()->where('name', 'Rack autorizado')->firstOrFail();
        $this->patch(route('warehouse.locations.update', $location), ['name' => 'Rack actualizado', 'level' => 2, 'active' => 1])
            ->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame('Rack actualizado', $location->fresh()->name);
        $this->get(route('users.index'))->assertForbidden();
        $this->patch(route('users.permissions', $user), ['module_permissions' => ['ubicaciones_almacen']])->assertForbidden();

        $this->actingAs($superadmin)->patch(route('users.permissions', $user), [])->assertRedirect();
        $this->actingAs($user->fresh())->get(route('warehouse.locations.index', ['cost_center_id' => $center->id]))->assertForbidden();
    }

    public function test_new_warehouse_user_can_receive_the_special_permission(): void
    {
        $superadmin = User::factory()->create(['role' => 'superadmin', 'department' => 'sistemas', 'active' => true]);
        $this->actingAs($superadmin)->post(route('users.store'), [
            'name' => 'Almacen autorizado', 'username' => 'almacen-autorizado', 'password' => 'secret123',
            'role' => 'user', 'department' => 'almacen', 'active' => 1,
            'special_permissions' => 1, 'module_permissions' => ['ubicaciones_almacen'],
        ])->assertSessionHasNoErrors()->assertRedirect();
        $this->assertTrue(User::where('username', 'almacen-autorizado')->firstOrFail()->canManageWarehouseLocations());
    }

    public function test_location_change_requires_permission_and_preserves_material_quantity(): void
    {
        $user = $this->warehouseUser();
        $center = $user->costCenters()->firstOrFail();
        $material = $this->entryMaterial($center);
        $location = $center->warehouseLocations()->create(['name' => 'Rack destino', 'level' => 1, 'active' => true]);
        $url = route('warehouse.materials.location', $material);
        $data = ['cost_center_id' => $center->id, 'warehouse_location_id' => $location->id];

        $this->actingAs($user)->get(route('warehouse.inventory', ['cost_center_id' => $center->id]))
            ->assertOk()->assertDontSee('Guardar ubicación');
        $this->patch($url, $data)->assertForbidden();
        $user->update(['module_permissions' => ['ubicaciones_almacen']]);
        $this->actingAs($user->fresh())->get(route('warehouse.inventory', ['cost_center_id' => $center->id]))
            ->assertOk()->assertSee('Guardar ubicación')->assertSee($url, false);
        $this->patch($url, $data + ['quantity' => 99])->assertSessionHasNoErrors()
            ->assertRedirect(route('warehouse.inventory', ['cost_center_id' => $center->id]));
        $this->assertEquals($location->id, $material->fresh()->warehouse_location_id);
        $this->assertSame('5.00', $material->fresh()->quantity);
        $this->assertSame($center->id, $material->fresh()->entry->cost_center_id);
    }

    public function test_special_permission_cannot_change_materials_outside_authorized_centers_or_use_an_invalid_destination(): void
    {
        $user = $this->warehouseUser();
        $user->update(['module_permissions' => ['ubicaciones_almacen']]);
        $center = $user->costCenters()->firstOrFail();
        $other = CostCenter::whereKeyNot($center->id)->firstOrFail();
        $material = $this->entryMaterial($center);
        $privateMaterial = $this->entryMaterial($other);
        $otherLocation = $other->warehouseLocations()->firstOrFail();
        $inactive = $center->warehouseLocations()->create(['name' => 'Ubicacion inactiva', 'level' => 1, 'active' => false]);

        $this->actingAs($user)->patch(route('warehouse.materials.location', $privateMaterial), [
            'cost_center_id' => $center->id, 'warehouse_location_id' => $otherLocation->id,
        ])->assertForbidden();
        $this->get(route('warehouse.locations.index', ['cost_center_id' => $other->id]))->assertForbidden();
        foreach ([$otherLocation->id, $inactive->id, 999999] as $destination) {
            $this->patch(route('warehouse.materials.location', $material), [
                'cost_center_id' => $center->id, 'warehouse_location_id' => $destination,
            ])->assertSessionHasErrors('warehouse_location_id');
        }
        $this->assertEquals($material->warehouse_location_id, $material->fresh()->warehouse_location_id);
    }

    public function test_location_permission_does_not_grant_other_departments_warehouse_management(): void
    {
        $user = User::factory()->create(['role' => 'user', 'department' => 'compras', 'active' => true, 'module_permissions' => ['ubicaciones_almacen']]);
        $this->assertFalse($user->canManageWarehouseLocations());
        $this->actingAs($user)->get(route('warehouse.locations.index'))->assertForbidden();
        $user->update(['department' => 'almacen', 'active' => false]);
        $this->assertFalse($user->canManageWarehouseLocations());
    }

    private function warehouseUser(): User
    {
        $user = User::factory()->create(['role' => 'user', 'department' => 'almacen', 'active' => true]);
        $user->costCenters()->sync([CostCenter::firstOrFail()->id]);

        return $user;
    }

    private function entryMaterial(CostCenter $center): WarehouseEntryMaterial
    {
        $part = Part::firstOrCreate(['name' => 'Material de prueba'], ['active' => true]);
        $entry = WarehouseEntry::create(['cost_center_id' => $center->id, 'entry_key' => 'ENT-'.$center->id.'-'.$part->id, 'entry_type' => 'Compra', 'entry_date' => now()]);

        return $entry->materials()->create(['part_id' => $part->id, 'quantity' => 5, 'warehouse_location_id' => $center->warehouseLocations()->firstOrFail()->id]);
    }
}
