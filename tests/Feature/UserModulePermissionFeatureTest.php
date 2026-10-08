<?php

namespace Tests\Feature;

use App\Models\CostCenter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserModulePermissionFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_conductores_permission_grants_a_regular_user_access_to_the_driver_module(): void
    {
        $user = User::create([
            'name' => 'Usuario Conductores',
            'username' => 'usuario-conductores',
            'password' => 'secret',
            'role' => 'user',
            'department' => 'compras',
            'module_permissions' => ['conductores'],
            'active' => true,
        ]);

        $this->actingAs($user)
            ->get(route('drivers.index'))
            ->assertOk()
            ->assertSee('Conductores')
            ->assertSee(route('drivers.index'));

        $this->actingAs($user)
            ->get(route('drivers.create'))
            ->assertOk()
            ->assertSee('Tipo A')
            ->assertSee('Tipo B')
            ->assertSee('Tipo C')
            ->assertSee('Tipo F')
            ->assertSee('name="license_expires_at"', false);
    }

    public function test_regular_user_without_conductores_permission_cannot_access_driver_module(): void
    {
        $user = User::create([
            'name' => 'Usuario Sin Conductores',
            'username' => 'usuario-sin-conductores',
            'password' => 'secret',
            'role' => 'user',
            'department' => 'compras',
            'active' => true,
        ]);

        $this->actingAs($user)->get(route('drivers.index'))->assertForbidden();
    }

    public function test_superadmin_can_update_additional_module_permissions(): void
    {
        $superadmin = User::create([
            'name' => 'Super Admin',
            'username' => 'superadmin-permissions',
            'password' => 'secret',
            'role' => 'superadmin',
            'department' => 'sistemas',
            'active' => true,
        ]);

        $user = User::create([
            'name' => 'Usuario Compras',
            'username' => 'usuario-compras',
            'password' => 'secret',
            'role' => 'user',
            'department' => 'compras',
            'active' => true,
        ]);

        $response = $this->actingAs($superadmin)->patch(route('users.permissions', $user), [
            'module_permissions' => ['rrhh', 'almacen', 'conductores'],
        ]);

        $response->assertRedirect(route('users.index', ['selected_user' => $user->id]));
        $this->assertSame(['rrhh', 'almacen', 'conductores'], $user->fresh()->grantedModules());
    }

    public function test_superadmin_can_create_user_with_special_permissions(): void
    {
        $superadmin = User::create([
            'name' => 'Super Admin',
            'username' => 'superadmin-create-permissions',
            'password' => 'secret',
            'role' => 'superadmin',
            'department' => 'sistemas',
            'active' => true,
        ]);

        $charcas = CostCenter::where('code', 'ZARPEO-CHARCAS')->firstOrFail();
        $matriz = CostCenter::where('code', 'INDIRECTOS-MATRIZ')->firstOrFail();

        $response = $this->actingAs($superadmin)->post(route('users.store'), [
            'name' => 'Usuario Nuevo',
            'username' => 'usuario-nuevo',
            'password' => 'secret123',
            'role' => 'user',
            'department' => 'compras',
            'active' => '1',
            'special_permissions' => '1',
            'module_permissions' => ['rrhh', 'almacen'],
            'cost_center_ids' => [$charcas->id, $matriz->id],
        ]);

        $createdUser = User::where('username', 'usuario-nuevo')->firstOrFail();

        $response->assertRedirect(route('users.index'));
        $this->assertSame(['rrhh', 'almacen'], $createdUser->grantedModules());
        $this->assertEqualsCanonicalizing(
            [$charcas->id, $matriz->id],
            $createdUser->costCenters()->pluck('cost_centers.id')->all()
        );
    }

    public function test_superadmin_can_replace_a_users_cost_center_assignments(): void
    {
        $superadmin = User::create([
            'name' => 'Super Admin',
            'username' => 'superadmin-update-centers',
            'password' => 'secret',
            'role' => 'superadmin',
            'department' => 'sistemas',
            'active' => true,
        ]);
        $user = User::create([
            'name' => 'Usuario Almacén',
            'username' => 'usuario-update-centers',
            'password' => 'secret',
            'role' => 'user',
            'department' => 'almacen',
            'active' => true,
        ]);
        $charcas = CostCenter::where('code', 'ZARPEO-CHARCAS')->firstOrFail();
        $sanMartin = CostCenter::where('code', 'ZARPEO-SAN-MARTIN')->firstOrFail();
        $matriz = CostCenter::where('code', 'INDIRECTOS-MATRIZ')->firstOrFail();
        $user->costCenters()->sync([$matriz->id]);

        $this->actingAs($superadmin)->put(route('users.update', $user), [
            'name' => $user->name,
            'username' => $user->username,
            'role' => $user->role,
            'department' => $user->department,
            'active' => '1',
            'cost_center_ids' => [$charcas->id, $sanMartin->id],
        ])->assertRedirect();

        $this->assertEqualsCanonicalizing(
            [$charcas->id, $sanMartin->id],
            $user->costCenters()->pluck('cost_centers.id')->all()
        );
    }
}
