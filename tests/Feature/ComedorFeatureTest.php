<?php

namespace Tests\Feature;

use App\Models\ComedorRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ComedorFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_guest_can_create_a_comedor_record(): void
    {
        $response = $this->post('/comedor', [
            'name' => 'Juan Perez',
        ]);

        $response->assertRedirect(route('comedor.index'));

        $this->assertDatabaseHas('comedor_records', [
            'name' => 'Juan Perez',
        ]);
    }

    public function test_guest_is_redirected_to_login_when_trying_to_view_comedor_records(): void
    {
        $response = $this->get('/rrhh/registrosComedor');

        $response->assertRedirect(route('login'));
    }

    public function test_admin_can_view_comedor_records(): void
    {
        $admin = User::create([
            'name' => 'Administrador',
            'username' => 'admin-comedor',
            'password' => 'secret',
            'role' => 'admin',
            'department' => 'sistemas',
            'active' => true,
        ]);

        ComedorRecord::create([
            'name' => 'Maria Lopez',
            'recorded_at' => now('America/Mexico_City')->utc(),
        ]);

        $response = $this->actingAs($admin)->get('/rrhh/registrosComedor');

        $response->assertOk();
        $response->assertSee('Registros');
        $response->assertSee('Maria Lopez');
    }

    public function test_regular_user_cannot_view_comedor_records(): void
    {
        $user = User::create([
            'name' => 'Usuario',
            'username' => 'usuario-comedor',
            'password' => 'secret',
            'role' => 'user',
            'department' => 'comedor',
            'active' => true,
        ]);

        $response = $this->actingAs($user)->get('/rrhh/registrosComedor');

        $response->assertForbidden();
    }
}
