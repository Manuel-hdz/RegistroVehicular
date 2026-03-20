<?php

namespace Tests\Feature;

use App\Models\Driver;
use App\Models\Movement;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DriverDestroyFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_superadmin_can_delete_driver_without_movements(): void
    {
        $superadmin = User::create([
            'name' => 'Super Admin',
            'username' => 'superadmin-driver-delete',
            'password' => 'secret',
            'role' => 'superadmin',
            'department' => 'sistemas',
            'active' => true,
        ]);

        $driver = Driver::create([
            'name' => 'Conductor sin historial',
            'active' => true,
        ]);

        $response = $this->actingAs($superadmin)->delete(route('drivers.destroy', $driver));

        $response->assertRedirect(route('drivers.index'));
        $response->assertSessionHas('status', 'Conductor eliminado.');
        $this->assertDatabaseMissing('drivers', ['id' => $driver->id]);
    }

    public function test_superadmin_deactivates_driver_when_has_movements(): void
    {
        $superadmin = User::create([
            'name' => 'Super Admin',
            'username' => 'superadmin-driver-deactivate',
            'password' => 'secret',
            'role' => 'superadmin',
            'department' => 'sistemas',
            'active' => true,
        ]);

        $driver = Driver::create([
            'name' => 'Conductor con historial',
            'active' => true,
        ]);

        $vehicle = Vehicle::create([
            'plate' => 'DRV-001',
            'identifier' => 'Unidad DRV-001',
            'active' => true,
        ]);

        Movement::create([
            'vehicle_id' => $vehicle->id,
            'driver_id' => $driver->id,
            'odometer_out' => 1500,
            'fuel_out' => 4,
            'departed_at' => now(),
            'destination' => 'Patio',
            'status' => 'open',
        ]);

        $response = $this->actingAs($superadmin)->delete(route('drivers.destroy', $driver));

        $response->assertRedirect(route('drivers.index'));
        $response->assertSessionHas('status', 'El conductor tiene movimientos registrados. Se marcó como inactivo.');
        $this->assertDatabaseHas('drivers', [
            'id' => $driver->id,
            'active' => false,
        ]);
        $this->assertDatabaseHas('movements', [
            'driver_id' => $driver->id,
        ]);
    }
}

