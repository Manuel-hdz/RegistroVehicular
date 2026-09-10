<?php

namespace Tests\Feature;

use App\Models\CostCenter;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserModulePermissionFeatureTest extends TestCase
{
    use RefreshDatabase;

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
            'module_permissions' => ['rrhh', 'almacen'],
        ]);

        $response->assertRedirect(route('users.index', ['selected_user' => $user->id]));
        $this->assertSame(['rrhh', 'almacen'], $user->fresh()->grantedModules());
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
