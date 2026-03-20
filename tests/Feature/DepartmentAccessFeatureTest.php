<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DepartmentAccessFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_warehouse_user_can_access_warehouse_and_shared_pending_view(): void
    {
        $user = User::create([
            'name' => 'Almacén',
            'username' => 'almacen-menu',
            'password' => 'secret',
            'role' => 'admin',
            'department' => 'almacen',
            'active' => true,
        ]);

        $this->actingAs($user)->get(route('parts.index'))->assertOk();
        $this->actingAs($user)->get(route('requisitions.pending'))->assertOk();
    }

    public function test_purchases_user_can_access_purchases_but_not_human_resources(): void
    {
        $user = User::create([
            'name' => 'Compras',
            'username' => 'compras-menu',
            'password' => 'secret',
            'role' => 'admin',
            'department' => 'compras',
            'active' => true,
        ]);

        $this->actingAs($user)->get(route('requisitions.pending'))->assertOk();
        $this->actingAs($user)->get(route('vehicles.index'))->assertOk();
        $this->actingAs($user)->get(route('personnel.index'))->assertForbidden();
    }

    public function test_maintenance_user_can_view_shared_modules_but_not_edit_owner_routes(): void
    {
        $user = User::create([
            'name' => 'Mantenimiento',
            'username' => 'mantenimiento-menu',
            'password' => 'secret',
            'role' => 'admin',
            'department' => 'mantenimiento',
            'active' => true,
        ]);

        $this->actingAs($user)->get(route('parts.index'))->assertOk();
        $this->actingAs($user)->get(route('requisitions.pending'))->assertOk();
        $this->actingAs($user)->get(route('vehicles.index'))->assertOk();
        $this->actingAs($user)->get(route('parts.create'))->assertForbidden();
    }

    public function test_management_user_can_access_all_operational_sections_but_not_configuration(): void
    {
        $user = User::create([
            'name' => 'Gerencia',
            'username' => 'gerencia-menu',
            'password' => 'secret',
            'role' => 'admin',
            'department' => 'gerencia',
            'active' => true,
        ]);

        $this->actingAs($user)->get(route('vehicles.index'))->assertOk();
        $this->actingAs($user)->get(route('maintenance.index'))->assertOk();
        $this->actingAs($user)->get(route('personnel.index'))->assertOk();
        $this->actingAs($user)->get(route('parts.index'))->assertOk();
        $this->actingAs($user)->get(route('requisitions.pending'))->assertOk();
        $this->actingAs($user)->get(route('vacation-policies.index'))->assertForbidden();
    }

    public function test_systems_user_can_access_configuration_and_operational_sections(): void
    {
        $user = User::create([
            'name' => 'Sistemas',
            'username' => 'sistemas-menu',
            'password' => 'secret',
            'role' => 'admin',
            'department' => 'sistemas',
            'active' => true,
        ]);

        $this->actingAs($user)->get(route('vacation-policies.index'))->assertOk();
        $this->actingAs($user)->get(route('vehicles.index'))->assertOk();
        $this->actingAs($user)->get(route('maintenance.index'))->assertOk();
        $this->actingAs($user)->get(route('personnel.index'))->assertOk();
        $this->actingAs($user)->get(route('parts.index'))->assertOk();
        $this->actingAs($user)->get(route('requisitions.pending'))->assertOk();
    }

    public function test_regular_user_role_can_only_access_registro_vehicular_routes(): void
    {
        $user = User::create([
            'name' => 'RRHH',
            'username' => 'rrhh-user',
            'password' => 'secret',
            'role' => 'user',
            'department' => 'recursos humanos',
            'active' => true,
        ]);

        $this->actingAs($user)->get(route('movements.index'))->assertOk();
        $this->actingAs($user)->get(route('personnel.index'))->assertForbidden();
        $this->actingAs($user)->get(route('vehicles.index'))->assertForbidden();
        $this->actingAs($user)->get(route('parts.index'))->assertForbidden();
        $this->actingAs($user)->get(route('requisitions.pending'))->assertForbidden();
    }
}
