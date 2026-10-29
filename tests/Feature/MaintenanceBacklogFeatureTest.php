<?php

namespace Tests\Feature;

use App\Models\CostCenter;
use App\Models\MaintenanceBacklogItem;
use App\Models\Requisition;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MaintenanceBacklogFeatureTest extends TestCase
{
    use RefreshDatabase;

    private function maintenanceUser(): User
    {
        return User::factory()->create(['active' => true, 'role' => 'admin', 'department' => 'mantenimiento']);
    }

    private function payload(Vehicle $vehicle, CostCenter $center): array
    {
        return [
            'vehicle_id' => $vehicle->id, 'cost_center_id' => $center->id,
            'description' => 'Bomba de prueba', 'quantity' => '2.50', 'unit' => 'Pieza',
            'unit_cost' => '123.45', 'system_type' => 'hidraulico',
            'criticality' => 'alto', 'status' => 'required',
        ];
    }

    public function test_pieces_and_requisitions_are_scoped_to_authorized_cost_center_and_equipment(): void
    {
        $user = $this->maintenanceUser();
        [$allowed, $other] = CostCenter::take(2)->get()->all();
        $user->costCenters()->sync([$allowed->id]);
        $vehicle = Vehicle::create(['cost_center_id' => $allowed->id, 'identifier' => 'EQ-BACKLOG', 'unit_name' => 'Unidad prueba', 'active' => true]);
        $anotherVehicle = Vehicle::create(['cost_center_id' => $allowed->id, 'identifier' => 'EQ-OTRO', 'active' => true]);
        MaintenanceBacklogItem::create($this->payload($vehicle, $allowed) + ['total_cost' => '308.63']);
        MaintenanceBacklogItem::create(array_replace($this->payload($vehicle, $other), ['description' => 'Pieza privada', 'total_cost' => '308.63']));
        MaintenanceBacklogItem::create(array_replace($this->payload($anotherVehicle, $allowed), ['description' => 'Pieza otro equipo', 'total_cost' => '308.63']));
        foreach ([$allowed, $other] as $center) {
            $requisition = Requisition::create(['cost_center_id' => $center->id, 'vehicle_id' => $vehicle->id, 'requester_name' => 'Prueba', 'status' => 'purchased']);
            $requisition->items()->create(['material_name' => $center->id === $allowed->id ? 'Solicitud visible' : 'Solicitud privada', 'quantity' => 1]);
        }

        $this->actingAs($user)->get(route('maintenance.backlog.index', ['vehicle_id' => $vehicle->id]))
            ->assertOk()->assertSee('Bomba de prueba')->assertSee('308.63')
            ->assertSee('bg-danger')->assertSee('Solicitud visible')
            ->assertDontSee('Pieza privada')->assertDontSee('Pieza otro equipo')->assertDontSee('Solicitud privada');
        $this->get(route('maintenance.backlog.index', ['cost_center_id' => $other->id]))->assertForbidden();
    }

    public function test_create_calculates_total_and_edit_updates_status_and_criticality(): void
    {
        $user = $this->maintenanceUser();
        $center = CostCenter::firstOrFail();
        $user->costCenters()->sync([$center->id]);
        $vehicle = Vehicle::create(['cost_center_id' => $center->id, 'identifier' => 'EQ-COSTO', 'active' => true]);
        $data = $this->payload($vehicle, $center);
        $this->actingAs($user)->post(route('maintenance.backlog.store'), $data + ['total_cost' => 1])
            ->assertRedirect(route('maintenance.backlog.index', ['cost_center_id' => $center->id, 'vehicle_id' => $vehicle->id]));
        $item = MaintenanceBacklogItem::firstOrFail();
        $this->assertSame('308.63', $item->total_cost);
        $this->assertSame($user->id, $item->created_by);
        $this->put(route('maintenance.backlog.update', $item), array_replace($data, ['status' => 'used', 'criticality' => 'bajo']))->assertRedirect();
        $this->assertDatabaseHas('maintenance_backlog_items', ['id' => $item->id, 'status' => 'used', 'criticality' => 'bajo']);
    }

    public function test_unauthorized_centers_cannot_be_used_to_create_or_modify_pieces(): void
    {
        $user = $this->maintenanceUser();
        [$allowed, $other] = CostCenter::take(2)->get()->all();
        $user->costCenters()->sync([$allowed->id]);
        $vehicle = Vehicle::create(['active' => true]);
        $item = MaintenanceBacklogItem::create($this->payload($vehicle, $other) + ['total_cost' => '308.63']);
        $this->actingAs($user)->post(route('maintenance.backlog.store'), $this->payload($vehicle, $other))->assertForbidden();
        $this->put(route('maintenance.backlog.update', $item), $this->payload($vehicle, $allowed))->assertForbidden();
        $this->assertDatabaseCount('maintenance_backlog_items', 1);
        $this->assertSame($other->id, $item->fresh()->cost_center_id);
    }

    public function test_validation_rejects_invalid_fields_and_inactive_equipment(): void
    {
        $user = $this->maintenanceUser();
        $center = CostCenter::firstOrFail();
        $user->costCenters()->sync([$center->id]);
        $vehicle = Vehicle::create(['cost_center_id' => $center->id, 'active' => false]);
        $this->actingAs($user)->post(route('maintenance.backlog.store'), array_replace($this->payload($vehicle, $center), [
            'quantity' => 0, 'unit_cost' => -1, 'criticality' => 'urgente', 'system_type' => 'invalid', 'status' => 'invalid',
        ]))->assertSessionHasErrors(['vehicle_id', 'quantity', 'unit_cost', 'criticality', 'system_type', 'status']);
        $this->assertDatabaseCount('maintenance_backlog_items', 0);
    }

    public function test_users_without_centers_see_empty_state_and_cannot_register(): void
    {
        $user = $this->maintenanceUser();
        $vehicle = Vehicle::create(['active' => true]);
        $this->actingAs($user)->get(route('maintenance.backlog.index'))->assertOk()->assertSee('No tienes centros de costos activos');
        $this->post(route('maintenance.backlog.store'), $this->payload($vehicle, CostCenter::firstOrFail()))->assertForbidden();
    }

    public function test_guest_and_users_without_maintenance_access_cannot_open_backlog(): void
    {
        $this->get(route('maintenance.backlog.index'))->assertRedirect(route('login'));
        $user = User::factory()->create(['active' => true, 'role' => 'user', 'department' => 'calidad']);
        $this->actingAs($user)->get(route('maintenance.backlog.index'))->assertForbidden();
    }

    public function test_center_filter_only_lists_assigned_equipment_and_rejects_mismatched_piece(): void
    {
        $user = $this->maintenanceUser();
        [$center, $other] = CostCenter::take(2)->get()->all();
        $user->costCenters()->sync([$center->id, $other->id]);
        Vehicle::create(['identifier' => 'EQUIPO-VISIBLE', 'cost_center_id' => $center->id, 'active' => true]);
        $hidden = Vehicle::create(['identifier' => 'EQUIPO-OCULTO', 'cost_center_id' => $other->id, 'active' => true]);
        Vehicle::create(['identifier' => 'EQUIPO-SIN-ASIGNAR', 'active' => true]);
        $this->actingAs($user)->get(route('maintenance.backlog.index', ['cost_center_id' => $center->id]))
            ->assertOk()->assertSee('EQUIPO-VISIBLE')->assertDontSee('EQUIPO-OCULTO')->assertDontSee('EQUIPO-SIN-ASIGNAR');
        $this->get(route('maintenance.backlog.index', ['cost_center_id' => $center->id, 'vehicle_id' => $hidden->id]))->assertNotFound();
        $this->post(route('maintenance.backlog.store'), $this->payload($hidden, $center))->assertSessionHasErrors('vehicle_id');
    }

    public function test_edit_cannot_move_a_piece_to_another_equipment_or_cost_center(): void
    {
        $user = $this->maintenanceUser();
        [$center, $other] = CostCenter::take(2)->get()->all();
        $user->costCenters()->sync([$center->id, $other->id]);
        $vehicle = Vehicle::create(['active' => true]);
        $otherVehicle = Vehicle::create(['active' => true]);
        $item = MaintenanceBacklogItem::create($this->payload($vehicle, $center) + ['total_cost' => '308.63']);

        $this->actingAs($user)->put(route('maintenance.backlog.update', $item), $this->payload($otherVehicle, $center))->assertForbidden();
        $this->put(route('maintenance.backlog.update', $item), $this->payload($vehicle, $other))->assertForbidden();
        $this->assertDatabaseHas('maintenance_backlog_items', ['id' => $item->id, 'vehicle_id' => $vehicle->id, 'cost_center_id' => $center->id]);
    }

    public function test_inactive_centers_are_not_available_even_when_assigned(): void
    {
        $user = $this->maintenanceUser();
        $center = CostCenter::firstOrFail();
        $center->update(['active' => false]);
        $user->costCenters()->sync([$center->id]);
        $vehicle = Vehicle::create(['active' => true]);
        $this->actingAs($user)->get(route('maintenance.backlog.index', ['cost_center_id' => $center->id]))->assertForbidden();
        $this->post(route('maintenance.backlog.store'), $this->payload($vehicle, $center))->assertForbidden();
    }

    public function test_vehicle_cost_center_can_be_set_and_updated_from_vehicle_forms(): void
    {
        $user = User::factory()->create(['active' => true, 'role' => 'superadmin', 'department' => 'sistemas']);
        [$center, $other] = CostCenter::take(2)->get()->all();
        $this->actingAs($user)->get(route('vehicles.create'))->assertOk()->assertSee('name="cost_center_id"', false);
        $this->post(route('vehicles.store'), ['identifier' => 'NUEVO-CENTRO', 'cost_center_id' => $center->id, 'active' => 1])->assertRedirect();
        $vehicle = Vehicle::where('identifier', 'NUEVO-CENTRO')->firstOrFail();
        $this->assertSame($center->id, $vehicle->costCenter->id);
        $this->get(route('vehicles.edit', $vehicle))->assertOk()->assertSee('name="cost_center_id"', false);
        $this->put(route('vehicles.update', $vehicle), ['cost_center_id' => $other->id, 'active' => 1])->assertRedirect();
        $this->assertSame($other->id, $vehicle->fresh()->costCenter->id);
        $this->get(route('vehicles.index', ['vehicle_id' => $vehicle->id]))->assertOk()->assertSee($other->name);
        $this->put(route('vehicles.update', $vehicle), ['cost_center_id' => 999999])->assertSessionHasErrors('cost_center_id');
    }
}
