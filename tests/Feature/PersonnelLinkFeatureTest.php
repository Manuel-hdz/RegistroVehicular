<?php

namespace Tests\Feature;

use App\Models\Driver;
use App\Models\Mechanic;
use App\Models\Personnel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PersonnelLinkFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_create_driver_from_registered_personnel(): void
    {
        $admin = User::create([
            'name' => 'Admin RRHH',
            'username' => 'admin-rrhh-drivers',
            'password' => 'secret',
            'role' => 'admin',
            'department' => 'rrhh',
            'active' => true,
        ]);

        $personnel = Personnel::create([
            'employee_number' => 'RH-100',
            'first_name' => 'Laura',
            'last_name' => 'Lopez',
            'middle_name' => 'Diaz',
            'active' => true,
        ]);

        $response = $this->actingAs($admin)->post(route('drivers.store'), [
            'personnel_id' => $personnel->id,
            'license' => 'LIC-1234',
            'active' => '1',
        ]);

        $driver = Driver::firstOrFail();

        $response->assertRedirect(route('drivers.index'));
        $this->assertSame($personnel->id, $driver->personnel_id);
        $this->assertSame('Laura Lopez Diaz', $driver->name);
        $this->assertSame('RH-100', $driver->employee_number);
        $this->assertSame('LIC-1234', $driver->license);
        $this->assertTrue($driver->active);
    }

    public function test_admin_can_create_mechanic_from_registered_personnel(): void
    {
        $admin = User::create([
            'name' => 'Admin Mantenimiento',
            'username' => 'admin-mecanicos',
            'password' => 'secret',
            'role' => 'admin',
            'department' => 'mantenimiento',
            'active' => true,
        ]);

        $personnel = Personnel::create([
            'employee_number' => 'RH-200',
            'first_name' => 'Carlos',
            'last_name' => 'Perez',
            'middle_name' => 'Soto',
            'active' => true,
        ]);

        $response = $this->actingAs($admin)->post(route('mechanics.store'), [
            'personnel_id' => $personnel->id,
            'daily_salary' => '850.50',
            'active' => '1',
        ]);

        $mechanic = Mechanic::firstOrFail();

        $response->assertRedirect(route('mechanics.index'));
        $this->assertSame($personnel->id, $mechanic->personnel_id);
        $this->assertSame('Carlos Perez Soto', $mechanic->name);
        $this->assertSame(850.5, (float) $mechanic->getRawOriginal('daily_salary'));
        $this->assertTrue($mechanic->active);
    }

    public function test_same_personnel_cannot_be_registered_twice_as_driver(): void
    {
        $admin = User::create([
            'name' => 'Admin RRHH',
            'username' => 'admin-rrhh-unique',
            'password' => 'secret',
            'role' => 'admin',
            'department' => 'rrhh',
            'active' => true,
        ]);

        $personnel = Personnel::create([
            'employee_number' => 'RH-300',
            'first_name' => 'Miguel',
            'last_name' => 'Ruiz',
            'middle_name' => 'Torres',
            'active' => true,
        ]);

        Driver::create([
            'personnel_id' => $personnel->id,
            'name' => $personnel->full_name,
            'employee_number' => $personnel->employee_number,
            'license' => 'LIC-0001',
            'active' => true,
        ]);

        $response = $this->from(route('drivers.create'))->actingAs($admin)->post(route('drivers.store'), [
            'personnel_id' => $personnel->id,
            'license' => 'LIC-9999',
            'active' => '1',
        ]);

        $response->assertRedirect(route('drivers.create'));
        $response->assertSessionHasErrors('personnel_id');
        $this->assertCount(1, Driver::all());
    }
}
