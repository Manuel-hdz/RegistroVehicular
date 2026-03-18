<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ConfigurationAccessFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_systems_department_user_can_access_configuration(): void
    {
        $user = User::create([
            'name' => 'Sistemas',
            'username' => 'sistemas-config',
            'password' => 'secret',
            'role' => 'admin',
            'department' => 'sistemas',
            'active' => true,
        ]);

        $response = $this->actingAs($user)->get(route('vacation-policies.index'));

        $response->assertOk();
    }

    public function test_non_systems_department_user_cannot_access_configuration(): void
    {
        $user = User::create([
            'name' => 'Almacen',
            'username' => 'almacen-config',
            'password' => 'secret',
            'role' => 'admin',
            'department' => 'almacen',
            'active' => true,
        ]);

        $response = $this->actingAs($user)->get(route('vacation-policies.index'));

        $response->assertForbidden();
    }
}
