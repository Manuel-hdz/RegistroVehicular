<?php

namespace Tests\Feature;

use App\Models\Personnel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PersonnelFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_store_extended_personnel_information(): void
    {
        $admin = User::create([
            'name' => 'Admin RRHH',
            'username' => 'admin-rrhh-personal',
            'password' => 'secret',
            'role' => 'admin',
            'department' => 'rrhh',
            'active' => true,
        ]);

        $response = $this->actingAs($admin)->post(route('personnel.store'), [
            'employee_number' => 'RH-501',
            'first_name' => 'Ana',
            'last_name' => 'Martinez',
            'middle_name' => 'Lopez',
            'marital_status' => 'Casado(a)',
            'sex' => 'Femenino',
            'birth_date' => '1992-08-14',
            'account_number' => '1234567890',
            'account_type' => 'Nomina',
            'active' => '1',
        ]);

        $personnel = Personnel::firstOrFail();

        $response->assertRedirect(route('personnel.index', ['personnel_id' => $personnel->id]));
        $this->assertSame('Casado(a)', $personnel->marital_status);
        $this->assertSame('Femenino', $personnel->sex);
        $this->assertSame('1992-08-14', optional($personnel->birth_date)->format('Y-m-d'));
        $this->assertSame('1234567890', $personnel->account_number);
        $this->assertSame('Nomina', $personnel->account_type);
    }

    public function test_admin_can_update_pending_vacation_days(): void
    {
        $admin = User::create([
            'name' => 'Admin RRHH',
            'username' => 'admin-rrhh-update',
            'password' => 'secret',
            'role' => 'admin',
            'department' => 'rrhh',
            'active' => true,
        ]);

        $personnel = Personnel::create([
            'employee_number' => 'RH-777',
            'first_name' => 'Laura',
            'last_name' => 'Campos',
            'active' => true,
            'pending_vacation_days' => 0,
        ]);

        $response = $this->actingAs($admin)->put(route('personnel.update', $personnel), [
            'employee_number' => 'RH-777',
            'first_name' => 'Laura',
            'last_name' => 'Campos',
            'pending_vacation_days' => 12,
            'active' => '1',
        ]);

        $response->assertRedirect(route('personnel.index', ['personnel_id' => $personnel->id]));
        $this->assertSame(12, $personnel->fresh()->pending_vacation_days);
    }
}
