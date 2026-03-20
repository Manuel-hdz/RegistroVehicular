<?php

namespace Tests\Feature;

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

        $response = $this->actingAs($superadmin)->post(route('users.store'), [
            'name' => 'Usuario Nuevo',
            'username' => 'usuario-nuevo',
            'password' => 'secret123',
            'role' => 'user',
            'department' => 'compras',
            'active' => '1',
            'special_permissions' => '1',
            'module_permissions' => ['rrhh', 'almacen'],
        ]);

        $createdUser = User::where('username', 'usuario-nuevo')->firstOrFail();

        $response->assertRedirect(route('users.index'));
        $this->assertSame(['rrhh', 'almacen'], $createdUser->grantedModules());
    }
}
