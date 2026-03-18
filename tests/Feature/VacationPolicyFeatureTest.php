<?php

namespace Tests\Feature;

use App\Models\Personnel;
use App\Models\User;
use App\Models\VacationPolicy;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VacationPolicyFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_update_fixed_vacation_policy_table(): void
    {
        $admin = $this->adminUser();

        $this->actingAs($admin)->get(route('vacation-policies.index'));

        $response = $this->actingAs($admin)->put(route('vacation-policies.update-table'), [
            'vacation_days' => [
                1 => 12,
                2 => 14,
                3 => 16,
                4 => 18,
                5 => 20,
                6 => 22,
                11 => 24,
                16 => 26,
                21 => 28,
                26 => 30,
                31 => 32,
            ],
        ]);

        $response->assertRedirect(route('vacation-policies.index'));
        $this->assertDatabaseHas('vacation_policies', [
            'service_year' => 1,
            'vacation_days' => 12,
            'active' => 1,
        ]);
        $this->assertDatabaseHas('vacation_policies', [
            'service_year' => 4,
            'vacation_days' => 18,
            'active' => 1,
        ]);
        $this->assertDatabaseHas('vacation_policies', [
            'service_year' => 31,
            'vacation_days' => 32,
            'active' => 1,
        ]);
    }

    public function test_personnel_profile_syncs_pending_vacation_days_from_policy_table(): void
    {
        Carbon::setTestNow('2026-03-18');

        $admin = $this->adminUser();
        VacationPolicy::create([
            'service_year' => 1,
            'vacation_days' => 12,
            'active' => true,
        ]);
        VacationPolicy::create([
            'service_year' => 2,
            'vacation_days' => 14,
            'active' => true,
        ]);

        $personnel = Personnel::create([
            'employee_number' => 'RH-920',
            'first_name' => 'Lucia',
            'last_name' => 'Martinez',
            'hire_date' => '2024-03-01',
            'pending_vacation_days' => 0,
            'vacation_years_awarded' => 0,
            'active' => true,
        ]);

        $response = $this->actingAs($admin)->get(route('personnel.index', [
            'personnel_id' => $personnel->id,
        ]));

        $response->assertOk();
        $this->assertSame(26, $personnel->fresh()->pending_vacation_days);
        $this->assertSame(2, $personnel->fresh()->vacation_years_awarded);

        Carbon::setTestNow();
    }

    private function adminUser(): User
    {
        return User::create([
            'name' => 'Administrador',
            'username' => 'admin-vacaciones',
            'password' => 'secret',
            'role' => 'admin',
            'department' => 'configuracion',
            'active' => true,
        ]);
    }
}
