<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\CostCenter;
use App\Models\Part;
use App\Models\User;
use App\Models\WarehouseEntry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class RecordAdministrationFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_permission_is_exclusive_to_active_superadmins_even_with_module_permissions(): void
    {
        foreach (['user', 'admin', 'superadmin'] as $role) {
            $user = $this->user($role, $role !== 'superadmin');
            $this->actingAs($user)->get(route('records.index'))->assertForbidden();
            $this->delete(route('records.destroy', ['table' => 'parts']), ['record' => 'forged'])->assertForbidden();
        }
        $this->actingAs($this->user('superadmin'))->get(route('records.index'))->assertOk()->assertSee('Eliminar registros');
    }

    public function test_superadmin_can_browse_every_table_without_exposing_passwords(): void
    {
        $admin = $this->user('superadmin');
        $this->actingAs($admin);
        foreach (Schema::getTables() as $table) {
            $response = $this->get(route('records.index', ['table' => $table['name']]));
            $response->assertOk();
            $this->assertNotEmpty($response->viewData('keyColumns'), $table['name']);
        }
        $this->get(route('records.index', ['table' => 'users']))->assertDontSee($admin->password);
        $this->get(route('records.index', ['table' => 'parts; DROP TABLE users']))->assertSessionHasErrors('table');
        $this->delete(route('records.destroy', ['table' => 'nonexistent']), ['record' => 'forged'])->assertNotFound();
    }

    public function test_superadmin_can_search_and_delete_one_record_with_audit_details(): void
    {
        $admin = $this->user('superadmin');
        $id = $this->meal('Registro a eliminar');
        $otherId = $this->meal('Registro conservado');
        $this->actingAs($admin);
        $token = $this->selection('comedor_records', 'Registro a eliminar');
        $this->delete(route('records.destroy', ['table' => 'comedor_records']), ['record' => $token])
            ->assertRedirect(route('records.index', ['table' => 'comedor_records']))->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('comedor_records', ['id' => $id]);
        $this->assertDatabaseHas('comedor_records', ['id' => $otherId]);
        $audit = AuditLog::where('action', 'deleted')->where('subject_type', 'comedor_records')->firstOrFail();
        $this->assertSame($admin->id, $audit->actor_id);
        $this->assertSame('Registro a eliminar', $audit->before_values['name']);
        $this->assertSame('delete_any_record', $audit->metadata['special_permission']);
    }

    public function test_forged_cross_table_and_stale_selections_cannot_delete_records(): void
    {
        $this->actingAs($this->user('superadmin'));
        $id = $this->meal('Original');
        $token = $this->selection('comedor_records', 'Original');
        $this->delete(route('records.destroy', ['table' => 'comedor_records']), ['record' => 'forged'])
            ->assertSessionHasErrors('record');
        $this->delete(route('records.destroy', ['table' => 'users']), ['record' => $token])->assertStatus(422);
        DB::table('comedor_records')->where('id', $id)->update(['name' => 'Actualizado']);
        $this->delete(route('records.destroy', ['table' => 'comedor_records']), ['record' => $token])
            ->assertSessionHasErrors('record');
        $this->assertDatabaseHas('comedor_records', ['id' => $id, 'name' => 'Actualizado']);
    }

    public function test_foreign_key_restrictions_are_reported_and_cascades_remain_consistent(): void
    {
        $this->actingAs($this->user('superadmin'));
        $part = Part::create(['clave' => 'DELETE-TEST', 'name' => 'Material protegido', 'unit_cost' => 0]);
        $entry = WarehouseEntry::create(['cost_center_id' => CostCenter::firstOrFail()->id, 'entry_date' => now()]);
        $line = $entry->materials()->create(['part_id' => $part->id, 'quantity' => 3]);
        $this->delete(route('records.destroy', ['table' => 'parts']), ['record' => $this->selection('parts', 'Material protegido')])
            ->assertSessionHasErrors('record');
        $this->assertDatabaseHas('parts', ['id' => $part->id]);
        $this->assertDatabaseHas('warehouse_entry_materials', ['id' => $line->id]);
        $this->delete(route('records.destroy', ['table' => 'warehouse_entries']), ['record' => $this->selection('warehouse_entries')])
            ->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('warehouse_entries', ['id' => $entry->id]);
        $this->assertDatabaseMissing('warehouse_entry_materials', ['id' => $line->id]);
        $this->assertDatabaseHas('parts', ['id' => $part->id]);
    }

    private function selection(string $table, string $search = ''): string
    {
        $response = $this->get(route('records.index', compact('table', 'search')))->assertOk();
        $records = $response->viewData('records');
        $this->assertCount(1, $records);

        return $records->first()['token'];
    }

    private function meal(string $name): int
    {
        return DB::table('comedor_records')->insertGetId(['name' => $name, 'recorded_at' => now(), 'created_at' => now(), 'updated_at' => now()]);
    }

    private function user(string $role, bool $active = true): User
    {
        return User::create([
            'name' => 'Prueba de eliminación', 'username' => 'delete-'.$role.'-'.User::count(),
            'password' => 'secret', 'role' => $role, 'department' => 'sistemas',
            'module_permissions' => array_keys(User::modulePermissionLabels()), 'active' => $active,
        ]);
    }
}
