<?php

namespace Tests\Feature;

use App\Models\CostCenter;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VehicleCostCenterFeatureTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::create([
            'name' => 'Administrador',
            'username' => 'admin-centros-vehiculos',
            'password' => 'secret',
            'role' => 'admin',
            'department' => 'mantenimiento',
            'active' => true,
        ]));
    }

    public function test_vehicle_can_be_created_reassigned_and_left_without_a_cost_center(): void
    {
        $first = CostCenter::create(['code' => 'VEH-01', 'name' => 'Centro vehicular uno', 'active' => true]);
        $second = CostCenter::create(['code' => 'VEH-02', 'name' => 'Centro vehicular dos', 'active' => true]);

        $this->get(route('vehicles.create'))
            ->assertOk()
            ->assertSee('name="cost_center_id"', false)
            ->assertSee($first->name);

        $this->post(route('vehicles.store'), [
            'plate' => 'CC-100',
            'cost_center_id' => $first->id,
        ])->assertSessionHasNoErrors();

        $vehicle = Vehicle::where('plate', 'CC-100')->firstOrFail();
        $this->assertEquals($first->id, $vehicle->cost_center_id);

        $this->get(route('vehicles.index', ['vehicle_id' => $vehicle->id]))
            ->assertOk()
            ->assertSee($first->name);

        $this->put(route('vehicles.update', $vehicle), ['cost_center_id' => $second->id])
            ->assertSessionHasNoErrors();
        $this->assertEquals($second->id, $vehicle->fresh()->cost_center_id);

        $this->put(route('vehicles.update', $vehicle), ['cost_center_id' => ''])
            ->assertSessionHasNoErrors();
        $this->assertNull($vehicle->fresh()->cost_center_id);
    }

    public function test_edit_preserves_an_assigned_inactive_center_and_creation_only_offers_active_centers(): void
    {
        $center = CostCenter::create(['code' => 'VEH-03', 'name' => 'Centro vehicular inactivo', 'active' => false]);
        $vehicle = Vehicle::create(['plate' => 'CC-200', 'cost_center_id' => $center->id, 'active' => true]);

        $this->get(route('vehicles.create'))->assertOk()->assertDontSee($center->name);

        $this->get(route('vehicles.edit', $vehicle))
            ->assertOk()
            ->assertSee('value="'.$center->id.'" selected', false)
            ->assertSee($center->name);
    }

    public function test_unknown_cost_center_is_rejected_when_creating_or_updating_a_vehicle(): void
    {
        $unknownId = (int) CostCenter::max('id') + 1;
        $vehicle = Vehicle::create(['plate' => 'CC-300', 'active' => true]);

        $this->post(route('vehicles.store'), ['plate' => 'CC-INVALID', 'cost_center_id' => $unknownId])
            ->assertSessionHasErrors('cost_center_id');
        $this->assertDatabaseMissing('vehicles', ['plate' => 'CC-INVALID']);

        $this->put(route('vehicles.update', $vehicle), ['cost_center_id' => $unknownId])
            ->assertSessionHasErrors('cost_center_id');
        $this->assertNull($vehicle->fresh()->cost_center_id);
    }

    public function test_assigning_a_cost_center_as_superadmin_keeps_vehicle_active_and_visible_in_backlog(): void
    {
        $user = User::where('username', 'admin-centros-vehiculos')->firstOrFail();
        $user->update(['role' => 'superadmin']);
        $this->actingAs($user);
        $center = CostCenter::firstOrFail();
        $user->costCenters()->sync([$center->id]);
        $vehicle = Vehicle::create(['identifier' => 'UNIDAD-BACKLOG-ACTIVA', 'active' => true]);

        $this->get(route('vehicles.edit', $vehicle))->assertOk()
            ->assertSee('name="active" value="1" checked', false);
        $this->put(route('vehicles.update', $vehicle), ['cost_center_id' => $center->id])
            ->assertSessionHasNoErrors();
        $this->assertTrue($vehicle->fresh()->active);
        $this->get(route('maintenance.backlog.index', ['cost_center_id' => $center->id]))
            ->assertOk()->assertSee('UNIDAD-BACKLOG-ACTIVA');

        $this->put(route('vehicles.update', $vehicle), ['active' => '0'])->assertSessionHasNoErrors();
        $this->assertFalse($vehicle->fresh()->active);
        $this->put(route('vehicles.update', $vehicle), ['cost_center_id' => $center->id])
            ->assertSessionHasNoErrors();
        $this->assertFalse($vehicle->fresh()->active);

        $this->put(route('vehicles.update', $vehicle), ['active' => '1'])->assertSessionHasNoErrors();
        $this->assertTrue($vehicle->fresh()->active);
        $this->get(route('maintenance.backlog.index', ['cost_center_id' => $center->id]))
            ->assertOk()->assertSee('UNIDAD-BACKLOG-ACTIVA');
    }
}
