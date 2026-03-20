<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ImpersonationPreviewFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_superadmin_can_preview_another_user_permissions(): void
    {
        $superadmin = User::create([
            'name' => 'Super Admin',
            'username' => 'superadmin-preview',
            'password' => 'secret',
            'role' => 'superadmin',
            'department' => 'sistemas',
            'active' => true,
        ]);

        $warehouseUser = User::create([
            'name' => 'Usuario Almacen',
            'username' => 'almacen-preview',
            'password' => 'secret',
            'role' => 'user',
            'department' => 'almacen',
            'active' => true,
        ]);

        $this->actingAs($superadmin)->post(route('impersonation.start'), [
            'preview_user_id' => $warehouseUser->id,
        ])->assertRedirect(route('public.dashboard'));

        $this->actingAs($superadmin)
            ->withSession([
                'impersonation.origin_user_id' => $superadmin->id,
                'impersonation.preview_user_id' => $warehouseUser->id,
            ])
            ->get(route('public.dashboard'))
            ->assertOk()
            ->assertSee('Vista activa como ' . $warehouseUser->name)
            ->assertDontSee(route('parts.index'))
            ->assertDontSee('Recursos Humanos')
            ->assertDontSee('Compras');

        $this->actingAs($superadmin)
            ->withSession([
                'impersonation.origin_user_id' => $superadmin->id,
                'impersonation.preview_user_id' => $warehouseUser->id,
            ])
            ->get(route('parts.index'))
            ->assertForbidden();

        $this->actingAs($superadmin)
            ->withSession([
                'impersonation.origin_user_id' => $superadmin->id,
                'impersonation.preview_user_id' => $warehouseUser->id,
            ])
            ->get(route('movements.index'))
            ->assertOk();

        $this->actingAs($superadmin)
            ->withSession([
                'impersonation.origin_user_id' => $superadmin->id,
                'impersonation.preview_user_id' => $warehouseUser->id,
            ])
            ->get(route('vacation-policies.index'))
            ->assertForbidden();
    }

    public function test_preview_mode_blocks_writes_even_if_previewed_user_has_access(): void
    {
        $superadmin = User::create([
            'name' => 'Super Admin',
            'username' => 'superadmin-readonly',
            'password' => 'secret',
            'role' => 'superadmin',
            'department' => 'sistemas',
            'active' => true,
        ]);

        $warehouseUser = User::create([
            'name' => 'Usuario Almacen',
            'username' => 'almacen-readonly',
            'password' => 'secret',
            'role' => 'admin',
            'department' => 'almacen',
            'active' => true,
        ]);

        $this->actingAs($superadmin)
            ->withSession([
                'impersonation.origin_user_id' => $superadmin->id,
                'impersonation.preview_user_id' => $warehouseUser->id,
            ])
            ->post(route('parts.store'), [
                'name' => 'Filtro',
                'unit_cost' => '10.50',
                'active' => '1',
            ])
            ->assertForbidden();
    }

    public function test_superadmin_can_stop_preview_and_recover_full_access(): void
    {
        $superadmin = User::create([
            'name' => 'Super Admin',
            'username' => 'superadmin-stop-preview',
            'password' => 'secret',
            'role' => 'superadmin',
            'department' => 'sistemas',
            'active' => true,
        ]);

        $warehouseUser = User::create([
            'name' => 'Usuario Almacen',
            'username' => 'almacen-stop-preview',
            'password' => 'secret',
            'role' => 'user',
            'department' => 'almacen',
            'active' => true,
        ]);

        $this->actingAs($superadmin)
            ->withSession([
                'impersonation.origin_user_id' => $superadmin->id,
                'impersonation.preview_user_id' => $warehouseUser->id,
            ])
            ->delete(route('impersonation.stop'))
            ->assertRedirect(route('public.dashboard'));

        $this->actingAs($superadmin)->get(route('vacation-policies.index'))->assertOk();
    }

    public function test_preview_user_with_different_session_id_does_not_force_logout(): void
    {
        $superadmin = User::create([
            'name' => 'Super Admin',
            'username' => 'superadmin-single-session',
            'password' => 'secret',
            'role' => 'superadmin',
            'department' => 'sistemas',
            'active' => true,
        ]);

        $previewUser = User::create([
            'name' => 'Usuario Vista',
            'username' => 'usuario-vista',
            'password' => 'secret',
            'role' => 'user',
            'department' => 'almacen',
            'active' => true,
            'current_session_id' => 'another-device-session',
        ]);

        $this->actingAs($superadmin)
            ->withSession([
                'impersonation.origin_user_id' => $superadmin->id,
                'impersonation.preview_user_id' => $previewUser->id,
            ])
            ->get(route('movements.index'))
            ->assertOk();
    }
}
