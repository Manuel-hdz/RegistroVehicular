<?php

namespace Tests\Feature;

use App\Models\CostCenter;
use App\Models\Requisition;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RequisitionFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_public_user_can_submit_requisition_with_items(): void
    {
        $costCenter = CostCenter::create([
            'code' => 'CC-01',
            'name' => 'Mantenimiento planta',
            'active' => true,
        ]);

        $vehicle = Vehicle::create([
            'plate' => 'ABC-100',
            'identifier' => 'Unidad 100',
            'active' => true,
        ]);

        $response = $this->post(route('requisitions.store'), [
            'requester_name' => 'Luis Herrera',
            'cost_center_id' => $costCenter->id,
            'items' => [
                [
                    'material_name' => 'Balatas delanteras',
                    'quantity' => '2',
                    'equipment_vehicle_id' => $vehicle->id,
                    'justification' => 'Se requiere cambio por desgaste.',
                ],
                [
                    'material_name' => 'Aceite hidraulico',
                    'quantity' => '1',
                    'equipment_vehicle_id' => '',
                    'justification' => 'Reposicion de almacen.',
                ],
            ],
        ]);

        $requisition = Requisition::with('items')->firstOrFail();

        $response->assertRedirect(route('requisitions.create'));
        $this->assertSame('Luis Herrera', $requisition->requester_name);
        $this->assertSame($costCenter->id, $requisition->cost_center_id);
        $this->assertNull($requisition->vehicle_id);
        $this->assertSame('pending', $requisition->status);
        $this->assertCount(2, $requisition->items);
        $this->assertSame('Balatas delanteras', $requisition->items[0]->material_name);
    }

    public function test_admin_can_create_cost_center(): void
    {
        $admin = User::create([
            'name' => 'Admin Compras',
            'username' => 'admin-compras',
            'password' => 'secret',
            'role' => 'admin',
            'department' => 'compras',
            'active' => true,
        ]);

        $response = $this->actingAs($admin)->post(route('cost-centers.store'), [
            'code' => 'CC-02',
            'name' => 'Obra civil',
            'active' => '1',
        ]);

        $response->assertRedirect(route('cost-centers.index'));
        $this->assertDatabaseHas('cost_centers', [
            'code' => 'CC-02',
            'name' => 'Obra civil',
            'active' => true,
        ]);
    }

    public function test_admin_can_see_pending_requisitions(): void
    {
        $admin = User::create([
            'name' => 'Admin Compras',
            'username' => 'admin-pendientes',
            'password' => 'secret',
            'role' => 'admin',
            'department' => 'compras',
            'active' => true,
        ]);

        $costCenter = CostCenter::create([
            'code' => 'CC-03',
            'name' => 'Produccion',
            'active' => true,
        ]);

        $requisition = Requisition::create([
            'cost_center_id' => $costCenter->id,
            'requester_name' => 'Maria Soto',
            'status' => 'pending',
        ]);

        $requisition->items()->create([
            'material_name' => 'Manguera industrial',
            'quantity' => '3',
            'justification' => 'Remplazo de material danado.',
        ]);

        $response = $this->actingAs($admin)->get(route('requisitions.pending'));

        $response->assertOk();
        $response->assertSee('Pendientes');
        $response->assertSee('Maria Soto');
        $response->assertSee('Manguera industrial');
    }

    public function test_admin_can_update_requisition_status(): void
    {
        $admin = User::create([
            'name' => 'Admin Compras',
            'username' => 'admin-status',
            'password' => 'secret',
            'role' => 'admin',
            'department' => 'compras',
            'active' => true,
        ]);

        $costCenter = CostCenter::create([
            'code' => 'CC-04',
            'name' => 'Taller',
            'active' => true,
        ]);

        $requisition = Requisition::create([
            'cost_center_id' => $costCenter->id,
            'requester_name' => 'Jose Luis',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($admin)->patch(route('requisitions.status', $requisition), [
            'status' => 'approved',
            'status_context' => 'pending',
        ]);

        $response->assertRedirect(route('requisitions.pending', ['status' => 'pending']));
        $this->assertSame('approved', $requisition->fresh()->status);
    }
}
