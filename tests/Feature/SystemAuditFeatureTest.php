<?php

namespace Tests\Feature;

use App\Models\AuditLog;
use App\Models\CostCenter;
use App\Models\Part;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\WarehouseEntry;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class SystemAuditFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_changes_record_the_actor_and_before_and_after_values_once(): void
    {
        $user = $this->superadmin();
        $this->actingAs($user);
        $this->post(route('vehicles.store'), ['identifier' => 'AUDIT-ORIGINAL', 'active' => 1])->assertRedirect();
        $vehicle = Vehicle::where('identifier', 'AUDIT-ORIGINAL')->firstOrFail();
        $this->assertSame(1, AuditLog::where('subject_type', 'vehicles')->where('subject_id', $vehicle->id)->where('action', 'created')->count());
        $created = AuditLog::where('subject_type', 'vehicles')->where('subject_id', $vehicle->id)->where('action', 'created')->firstOrFail();
        $this->assertEquals($user->id, $created->actor_id);
        $this->assertSame($user->username, $created->actor_username);
        $this->assertSame('AUDIT-ORIGINAL', $created->after_values['identifier']);

        $this->put(route('vehicles.update', $vehicle), ['identifier' => 'AUDIT-NUEVO'])->assertRedirect();
        $changed = AuditLog::where('subject_type', 'vehicles')->where('subject_id', $vehicle->id)->where('action', 'updated')->firstOrFail();
        $this->assertSame('AUDIT-ORIGINAL', $changed->before_values['identifier']);
        $this->assertSame('AUDIT-NUEVO', $changed->after_values['identifier']);
        $this->assertNotNull($changed->request_id);
        $this->assertSame($changed->request_id, AuditLog::where('action', 'request')->where('route', 'vehicles.update')->firstOrFail()->request_id);

        $this->delete(route('vehicles.destroy', $vehicle))->assertRedirect();
        $deleted = AuditLog::where('subject_type', 'vehicles')->where('subject_id', $vehicle->id)->where('action', 'deleted')->firstOrFail();
        $this->assertSame('AUDIT-NUEVO', $deleted->before_values['identifier']);
        $this->assertSame([], $deleted->after_values);
    }

    public function test_passwords_session_tokens_and_log_secrets_are_not_stored(): void
    {
        $user = $this->superadmin();
        $this->actingAs($user);
        $this->post(route('users.store'), [
            'name' => 'Usuario auditado', 'username' => 'usuario-auditado', 'password' => 'PrivatePassword123',
            'role' => 'user', 'department' => 'almacen', 'active' => 1,
        ])->assertRedirect();
        $createdUser = User::where('username', 'usuario-auditado')->firstOrFail();
        $entry = AuditLog::where('subject_type', 'users')->where('subject_id', $createdUser->id)->where('action', 'created')->firstOrFail();
        $this->assertSame('[OCULTO]', $entry->after_values['password']);
        $this->assertStringNotContainsString($createdUser->password, $entry->toJson());

        Log::warning('Prueba password=TextSecret token=TokenSecret', [
            'nested' => ['password' => 'NestedSecret', 'authorization' => 'Bearer AuthSecret'],
            'current_session_id' => 'SessionSecret', 'detail' => 'Información visible',
        ]);
        $log = AuditLog::where('action', 'log')->latest('id')->firstOrFail();
        $this->assertSame('warning', $log->metadata['level']);
        $this->assertSame('[OCULTO]', $log->metadata['context']['nested']['password']);
        $this->assertStringContainsString('Información visible', $log->toJson(JSON_UNESCAPED_UNICODE));
        foreach (['PrivatePassword123', 'TextSecret', 'TokenSecret', 'NestedSecret', 'AuthSecret', 'SessionSecret'] as $secret) {
            $this->assertStringNotContainsString($secret, AuditLog::all()->toJson());
        }
    }

    public function test_bulk_writes_and_cost_center_assignments_are_audited_and_rolled_back_with_the_data(): void
    {
        $user = $this->superadmin();
        $this->actingAs($user);
        $vehicle = Vehicle::create(['identifier' => 'MASIVO-ANTES', 'active' => true]);
        DB::table('vehicles')->where('id', $vehicle->id)->update(['identifier' => 'MASIVO-DESPUES']);
        app(\App\Services\SystemAudit::class)->flush();
        $this->assertDatabaseHas('audit_logs', ['action' => 'database.update', 'subject_type' => 'vehicles', 'actor_id' => $user->id]);
        $user->costCenters()->sync([CostCenter::firstOrFail()->id]);
        app(\App\Services\SystemAudit::class)->flush();
        $this->assertDatabaseHas('audit_logs', ['action' => 'database.insert', 'subject_type' => 'cost_center_user']);
        $count = AuditLog::count();
        try {
            DB::transaction(function (): void {
                Vehicle::create(['identifier' => 'AUDIT-ROLLBACK', 'active' => true]);
                DB::table('vehicles')->where('identifier', 'AUDIT-ROLLBACK')->update(['identifier' => 'AUDIT-ROLLBACK-MASIVO']);
                throw new \RuntimeException('Prueba de rollback');
            });
        } catch (\RuntimeException) {
            // El historial no debe presentar como guardado un cambio revertido.
        }
        $this->assertDatabaseMissing('vehicles', ['identifier' => 'AUDIT-ROLLBACK']);
        app(\App\Services\SystemAudit::class)->flush();
        $this->assertSame($count, AuditLog::count());
    }

    public function test_auditing_raw_inserts_preserves_the_returned_id(): void
    {
        $id = DB::table('vehicles')->insertGetId(['identifier' => 'AUDIT-ID-CORRECTO', 'active' => true]);
        $this->assertDatabaseHas('vehicles', ['id' => $id, 'identifier' => 'AUDIT-ID-CORRECTO']);
        app(\App\Services\SystemAudit::class)->flush();
        $this->assertDatabaseHas('audit_logs', ['action' => 'database.insert', 'subject_type' => 'vehicles']);
    }

    public function test_login_logout_failed_access_and_failed_requests_are_recorded(): void
    {
        $user = User::factory()->create(['username' => 'audit-login', 'active' => true]);
        $this->post(route('login.post'), ['username' => 'audit-login', 'password' => 'WrongPrivatePassword'])->assertSessionHasErrors('username');
        $this->assertDatabaseHas('audit_logs', ['action' => 'login_failed', 'actor_id' => $user->id]);
        $this->post(route('login.post'), ['username' => 'audit-login', 'password' => 'password'])->assertRedirect();
        $this->assertDatabaseHas('audit_logs', ['action' => 'login', 'actor_id' => $user->id]);
        $user->refresh()->update(['current_session_id' => null]);
        $this->actingAs($user->fresh());
        $this->get(route('audit-logs.index'))->assertForbidden();
        $this->assertDatabaseHas('audit_logs', ['action' => 'request', 'route' => 'audit-logs.index', 'status_code' => 403, 'actor_id' => $user->id]);
        $this->post(route('logout'))->assertRedirect();
        $this->assertDatabaseHas('audit_logs', ['action' => 'logout', 'actor_id' => $user->id]);
        $this->assertDatabaseHas('audit_logs', ['action' => 'request', 'route' => 'logout', 'actor_id' => $user->id]);
        $this->assertStringNotContainsString('WrongPrivatePassword', AuditLog::all()->toJson());
    }

    public function test_only_superadmin_can_view_the_bitacora_and_its_menu_link(): void
    {
        $this->get(route('audit-logs.index'))->assertRedirect(route('login'));
        foreach ([['admin', 'sistemas'], ['admin', 'almacen'], ['user', 'almacen']] as [$role, $department]) {
            $user = User::factory()->create(['role' => $role, 'department' => $department, 'active' => true]);
            $this->actingAs($user)->get(route('audit-logs.index'))->assertForbidden();
            $this->get(route('public.dashboard'))->assertOk()->assertDontSee('Bitácora del sistema');
        }
        $this->actingAs($this->superadmin())->get(route('audit-logs.index'))
            ->assertOk()->assertSee('Bitácora general del sistema')->assertSee('Bitácora del sistema');
    }

    public function test_bitacora_filters_by_actor_action_subject_and_mexico_city_dates(): void
    {
        $user = $this->superadmin();
        $other = User::factory()->create(['active' => true]);
        foreach ([
            ['VISIBLE-FILTRO', $user->id, 'created', 'vehicles', '2026-10-29 06:00:00'],
            ['OCULTO-DIA-ANTERIOR', $user->id, 'created', 'vehicles', '2026-10-29 05:59:59'],
            ['OCULTO-USUARIO', $other->id, 'created', 'vehicles', '2026-10-29 08:00:00'],
            ['OCULTO-ACCION', $user->id, 'updated', 'vehicles', '2026-10-29 08:00:00'],
            ['OCULTO-TIPO', $user->id, 'created', 'parts', '2026-10-29 08:00:00'],
        ] as [$description, $actor, $action, $subject, $date]) {
            AuditLog::create(['description' => $description, 'actor_id' => $actor, 'action' => $action, 'subject_type' => $subject, 'created_at' => $date]);
        }
        $this->actingAs($user)->get(route('audit-logs.index', [
            'actor_id' => $user->id, 'action' => 'created', 'subject_type' => 'vehicles',
            'date_from' => '2026-10-29', 'date_to' => '2026-10-29', 'search' => 'FILTRO',
        ]))->assertOk()->assertSee('VISIBLE-FILTRO')->assertDontSee('OCULTO-');
        $this->get(route('audit-logs.index', ['date_from' => '2026-10-29', 'date_to' => '2026-10-28']))->assertSessionHasErrors('date_to');
    }

    public function test_preview_requests_are_attributed_to_the_superadmin_without_exposing_the_audit_page(): void
    {
        $admin = $this->superadmin();
        $preview = User::factory()->create(['role' => 'user', 'department' => 'almacen', 'active' => true]);
        $this->actingAs($admin)->withSession([
            'impersonation.origin_user_id' => $admin->id,
            'impersonation.preview_user_id' => $preview->id,
        ])->get(route('public.dashboard'))->assertOk()->assertDontSee('Bitácora del sistema');
        $entry = AuditLog::where('action', 'request')->where('route', 'public.dashboard')->latest('id')->firstOrFail();
        $this->assertEquals($admin->id, $entry->actor_id);
        $this->assertEquals($preview->id, $entry->metadata['preview_user_id']);
        $this->actingAs($admin)->get(route('audit-logs.index'))->assertForbidden();
    }

    private function superadmin(): User
    {
        return User::factory()->create(['role' => 'superadmin', 'department' => 'sistemas', 'active' => true]);
    }

    public function test_superadmin_sees_entry_authors_in_inventory_and_recent_entries_without_crossing_centers(): void
    {
        $admin = $this->superadmin();
        $author = User::factory()->create(['name' => 'AUTOR-ENTRADA-VISIBLE', 'username' => 'autor-inventario-visible', 'active' => true]);
        [$center, $other] = CostCenter::take(2)->get()->all();
        $admin->costCenters()->sync([$center->id, $other->id]);
        $part = Part::create(['name' => 'Material con autor', 'active' => true]);
        foreach ([$center, $other] as $entryCenter) {
            $entry = WarehouseEntry::create([
                'cost_center_id' => $entryCenter->id, 'entry_key' => 'AUDIT-ENT-'.$entryCenter->id,
                'entry_type' => 'Compra', 'entry_date' => now(),
                'registered_by_user_id' => $entryCenter->id === $center->id ? $author->id : null,
                'registered_by_username' => $entryCenter->id === $center->id ? $author->username : 'AUTOR-OTRO-CENTRO',
            ]);
            $entry->materials()->create(['part_id' => $part->id, 'quantity' => 5, 'warehouse_location_id' => $entryCenter->warehouseLocations()->firstOrFail()->id]);
        }
        $this->actingAs($admin)->get(route('warehouse.inventory', ['cost_center_id' => $center->id]))
            ->assertOk()->assertSee('Registró')->assertSee($author->name)->assertSee($author->username)->assertDontSee('AUTOR-OTRO-CENTRO');
        $this->get(route('warehouse.movements', ['cost_center_id' => $center->id]))
            ->assertOk()->assertSee('Registró: '.$author->name)->assertDontSee('AUTOR-OTRO-CENTRO');
        $author->update(['role' => 'user', 'department' => 'almacen']);
        $author->costCenters()->sync([$center->id]);
        $this->actingAs($author->fresh())->get(route('warehouse.inventory', ['cost_center_id' => $center->id]))
            ->assertOk()->assertDontSee('Entrada #')->assertDontSee('Bitácora del sistema');
    }
}
