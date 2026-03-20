<?php

namespace Tests\Feature;

use App\Models\Driver;
use App\Models\Movement;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MovementFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_role_can_register_departure_without_guard_foreign_key_conflict(): void
    {
        $user = User::create([
            'name' => 'Usuario Operativo',
            'username' => 'usuario-operativo',
            'password' => 'secret',
            'role' => 'user',
            'department' => 'almacen',
            'active' => true,
        ]);

        $vehicle = Vehicle::create([
            'plate' => 'USR-100',
            'identifier' => 'Unidad U100',
            'active' => true,
        ]);

        $driver = Driver::create([
            'name' => 'Conductor Uno',
            'active' => true,
        ]);

        $response = $this->actingAs($user)->post(route('movements.store'), [
            'vehicle_id' => $vehicle->id,
            'driver_id' => $driver->id,
            'odometer_out' => 1000,
            'fuel_out_base' => '1/2',
            'fuel_out_dir' => 'exact',
            'departed_at' => now()->format('Y-m-d H:i:s'),
            'destination' => 'Patio principal',
            'notes_out' => 'Salida normal',
        ]);

        $response->assertRedirect(route('movements.index'));

        $movement = Movement::firstOrFail();
        $this->assertNull($movement->guard_out_id);
        $this->assertSame('open', $movement->status);
    }

    public function test_admin_role_keeps_guard_id_when_registering_departure(): void
    {
        $admin = User::create([
            'name' => 'Admin Operativo',
            'username' => 'admin-operativo',
            'password' => 'secret',
            'role' => 'admin',
            'department' => 'almacen',
            'active' => true,
        ]);

        $vehicle = Vehicle::create([
            'plate' => 'ADM-100',
            'identifier' => 'Unidad A100',
            'active' => true,
        ]);

        $driver = Driver::create([
            'name' => 'Conductor Dos',
            'active' => true,
        ]);

        $response = $this->actingAs($admin)->post(route('movements.store'), [
            'vehicle_id' => $vehicle->id,
            'driver_id' => $driver->id,
            'odometer_out' => 2000,
            'fuel_out_base' => '3/4',
            'fuel_out_dir' => 'exact',
            'departed_at' => now()->format('Y-m-d H:i:s'),
            'destination' => 'Planta',
            'notes_out' => 'Salida admin',
        ]);

        $response->assertRedirect(route('movements.index'));

        $movement = Movement::firstOrFail();
        $this->assertSame($admin->id, $movement->guard_out_id);
    }
}
