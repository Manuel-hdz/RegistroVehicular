<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\CostCenter;
use App\Models\Mechanic;
use App\Models\Part;
use App\Models\Repair;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\WarehouseEntry;
use App\Models\WarehouseMaterialExit;
use App\Services\WarehouseMaterialDeletion;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InlineRecordDeletionFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_inventory_delete_button_and_endpoint_are_exclusive_to_superadmin(): void
    {
        $center = CostCenter::firstOrFail();
        $part = $this->part('Visible');
        $this->entry($center, $part);
        foreach (['user', 'admin', 'superadmin'] as $role) {
            $user = $this->user($role);
            $user->costCenters()->sync([$center->id]);
            $response = $this->actingAs($user)->get(route('warehouse.inventory', ['cost_center_id' => $center->id]))->assertOk();
            if ($role === 'superadmin') {
                $response->assertSee('Eliminar material')->assertSee(route('warehouse.materials.destroy', $part));
            } else {
                $response->assertDontSee('Eliminar material');
                $this->delete(route('warehouse.materials.destroy', $part), ['cost_center_id' => $center->id])->assertForbidden();
                $this->assertDatabaseHas('parts', ['id' => $part->id]);
            }
        }
    }

    public function test_material_deletion_removes_related_rows_across_warehouses_and_preserves_shared_entries(): void
    {
        $admin = $this->user('superadmin');
        $centers = CostCenter::take(2)->get();
        $admin->costCenters()->sync($centers->pluck('id'));
        $part = $this->part('Eliminar');
        $other = $this->part('Conservar');
        $exclusiveEntry = $this->entry($centers[0], $part);
        $sharedEntry = $this->entry($centers[1], $part);
        $sharedLine = $sharedEntry->materials()->create(['part_id' => $other->id, 'quantity' => 4]);
        foreach ($centers as $center) {
            WarehouseMaterialExit::create([
                'cost_center_id' => $center->id, 'part_id' => $part->id, 'quantity' => 1,
                'exit_date' => now(), 'source' => 'Almacén', 'destination' => 'Obra', 'responsible' => 'Responsable',
            ]);
        }
        $vehicle = Vehicle::create(['identifier' => 'DELETE-RELATED', 'active' => true]);
        $repair = Repair::create(['vehicle_id' => $vehicle->id, 'duration_hours' => 1]);
        $repair->parts()->attach($part->id, ['quantity' => 1]);
        $repair->parts()->attach($other->id, ['quantity' => 2]);
        $this->actingAs($admin)->delete(route('warehouse.materials.destroy', $part), ['cost_center_id' => $centers[0]->id])
            ->assertRedirect(route('warehouse.inventory', ['cost_center_id' => $centers[0]->id]))->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('parts', ['id' => $part->id]);
        $this->assertDatabaseMissing('warehouse_entry_materials', ['part_id' => $part->id]);
        $this->assertDatabaseMissing('warehouse_material_exits', ['part_id' => $part->id]);
        $this->assertDatabaseMissing('repair_part', ['part_id' => $part->id]);
        $this->assertDatabaseMissing('warehouse_entries', ['id' => $exclusiveEntry->id]);
        $this->assertDatabaseHas('warehouse_entries', ['id' => $sharedEntry->id]);
        $this->assertDatabaseHas('warehouse_entry_materials', ['id' => $sharedLine->id, 'quantity' => 4]);
        $this->assertDatabaseHas('parts', ['id' => $other->id]);
        $this->assertDatabaseHas('repairs', ['id' => $repair->id]);
        $this->assertDatabaseHas('repair_part', ['repair_id' => $repair->id, 'part_id' => $other->id]);
        $this->assertDatabaseHas('audit_logs', ['actor_id' => $admin->id, 'subject_type' => 'parts', 'subject_id' => $part->id, 'action' => 'deleted']);
        $this->get(route('warehouse.movements', ['cost_center_id' => $centers[1]->id]))->assertOk()->assertSee('Conservar');
    }

    public function test_failed_material_deletion_rolls_back_its_related_records(): void
    {
        $part = $this->part('Rollback');
        $entry = $this->entry(CostCenter::firstOrFail(), $part);
        Part::deleting(function () {
            throw new \RuntimeException('Deletion failed');
        });
        try {
            app(WarehouseMaterialDeletion::class)->delete($part);
            $this->fail('Expected deletion to fail');
        } catch (\RuntimeException $exception) {
            $this->assertSame('Deletion failed', $exception->getMessage());
        }
        $this->assertDatabaseHas('parts', ['id' => $part->id]);
        $this->assertDatabaseHas('warehouse_entries', ['id' => $entry->id]);
        $this->assertDatabaseHas('warehouse_entry_materials', ['part_id' => $part->id]);
        $this->assertSame(0, AuditLog::where('action', 'deleted')->count());
    }

    public function test_inline_table_button_deletes_and_returns_to_the_same_screen(): void
    {
        $mechanic = Mechanic::create(['name' => 'Eliminar mecánico', 'daily_salary' => 500, 'active' => true]);
        $this->actingAs($this->user('superadmin'));
        $response = $this->get(route('mechanics.index'))->assertOk();
        $document = new \DOMDocument;
        @$document->loadHTML($response->getContent());
        $xpath = new \DOMXPath($document);
        $token = $xpath->query('//form[@action="'.route('records.destroy', ['table' => 'mechanics']).'"]/input[@name="record"]')->item(0)?->getAttribute('value');
        $this->assertNotEmpty($token);
        $this->delete(route('records.destroy', ['table' => 'mechanics']), ['record' => $token])
            ->assertRedirect(route('mechanics.index'))->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('mechanics', ['id' => $mechanic->id]);
        $this->actingAs($this->user('admin'))->get(route('mechanics.index'))->assertOk()->assertDontSee('name="record"', false);
    }

    private function part(string $name): Part
    {
        return Part::create(['name' => $name, 'clave' => $name, 'unit_cost' => 0, 'active' => true]);
    }

    private function entry(CostCenter $center, Part $part): WarehouseEntry
    {
        $entry = WarehouseEntry::create(['cost_center_id' => $center->id, 'entry_date' => now()]);
        $entry->materials()->create(['part_id' => $part->id, 'quantity' => 5]);

        return $entry;
    }

    private function user(string $role): User
    {
        return User::create([
            'name' => $role, 'username' => 'inline-'.$role.'-'.User::count(), 'password' => 'secret',
            'role' => $role, 'department' => $role === 'user' ? 'almacen' : 'sistemas', 'active' => true,
        ]);
    }
}
