<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class VehicleManagementFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_vehicle_with_documents_but_active_stays_true(): void
    {
        Storage::fake('local');

        $admin = User::create([
            'name' => 'Administrador',
            'username' => 'admin-vehiculos',
            'password' => 'secret',
            'role' => 'admin',
            'department' => 'mantenimiento',
            'active' => true,
        ]);

        $response = $this->actingAs($admin)->post(route('vehicles.store'), [
            'plate' => 'XYZ-100',
            'identifier' => 'Unidad 100',
            'model' => 'Hilux',
            'year' => 2023,
            'serial_number' => 'SERIE123',
            'additional_serial_number' => 'SERIE-ALT-01',
            'engine_number' => 'MOTOR-123',
            'supplier' => 'Proveedor Demo',
            'assigned_personnel' => 'Juan Perez',
            'description' => 'Unidad de prueba',
            'active' => '0',
            'photo' => UploadedFile::fake()->image('equipo.jpg'),
            'circulation_card' => UploadedFile::fake()->create('tarjeta.pdf', 120, 'application/pdf'),
            'insurance_policy' => UploadedFile::fake()->create('poliza.pdf', 120, 'application/pdf'),
        ]);

        $vehicle = Vehicle::where('plate', 'XYZ-100')->firstOrFail();

        $response->assertRedirect(route('vehicles.index', ['vehicle_id' => $vehicle->id]));
        $this->assertTrue($vehicle->active);
        $this->assertNotNull($vehicle->photo_path);
        $this->assertNotNull($vehicle->circulation_card_path);
        $this->assertNotNull($vehicle->insurance_policy_path);
        Storage::disk('local')->assertExists($vehicle->photo_path);
        Storage::disk('local')->assertExists($vehicle->circulation_card_path);
        Storage::disk('local')->assertExists($vehicle->insurance_policy_path);
    }

    public function test_superadmin_can_mark_vehicle_inactive(): void
    {
        $superadmin = User::create([
            'name' => 'Super Admin',
            'username' => 'super-vehiculos',
            'password' => 'secret',
            'role' => 'superadmin',
            'department' => 'sistemas',
            'active' => true,
        ]);

        $vehicle = Vehicle::create([
            'plate' => 'ABC-123',
            'identifier' => 'Unidad 1',
            'active' => true,
        ]);

        $response = $this->actingAs($superadmin)->put(route('vehicles.update', $vehicle), [
            'plate' => 'ABC-123',
            'identifier' => 'Unidad 1',
        ]);

        $response->assertRedirect(route('vehicles.index', ['vehicle_id' => $vehicle->id]));
        $this->assertFalse($vehicle->fresh()->active);
    }

    public function test_admin_cannot_mark_vehicle_inactive_on_update(): void
    {
        $admin = User::create([
            'name' => 'Administrador',
            'username' => 'admin-update-vehiculos',
            'password' => 'secret',
            'role' => 'admin',
            'department' => 'mantenimiento',
            'active' => true,
        ]);

        $vehicle = Vehicle::create([
            'plate' => 'DEF-456',
            'identifier' => 'Unidad 2',
            'active' => true,
        ]);

        $response = $this->actingAs($admin)->put(route('vehicles.update', $vehicle), [
            'plate' => 'DEF-456',
            'identifier' => 'Unidad 2',
        ]);

        $response->assertRedirect(route('vehicles.index', ['vehicle_id' => $vehicle->id]));
        $this->assertTrue($vehicle->fresh()->active);
    }

    public function test_vehicle_index_shows_profile_view(): void
    {
        $admin = User::create([
            'name' => 'Administrador',
            'username' => 'admin-consulta-vehiculos',
            'password' => 'secret',
            'role' => 'admin',
            'department' => 'almacen',
            'active' => true,
        ]);

        $vehicle = Vehicle::create([
            'plate' => 'GHI-789',
            'identifier' => 'Unidad Perfil',
            'serial_number' => 'SER-999',
            'engine_number' => 'MTR-999',
            'supplier' => 'Proveedor Perfil',
            'assigned_personnel' => 'Pedro Lopez',
            'description' => 'Descripcion perfil',
            'active' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('vehicles.index', ['vehicle_id' => $vehicle->id]));

        $response->assertOk();
        $response->assertSee('Consulta de unidades');
        $response->assertSee('Unidad Perfil');
        $response->assertSee('Proveedor Perfil');
        $response->assertSee('Pedro Lopez');
    }
}
