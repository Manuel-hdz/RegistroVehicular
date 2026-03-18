<?php

namespace Tests\Feature;

use App\Models\Personnel;
use App\Models\PersonnelCardexEntry;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CardexFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_vacation_code_consumes_and_restores_pending_days_when_changed(): void
    {
        $admin = $this->adminUser();
        $personnel = Personnel::create([
            'employee_number' => 'RH-910',
            'first_name' => 'Mario',
            'last_name' => 'Rojas',
            'hire_date' => '2024-01-15',
            'pending_vacation_days' => 2,
            'active' => true,
        ]);

        $firstResponse = $this->actingAs($admin)->post(route('cardex.store'), [
            'personnel_id' => $personnel->id,
            'entry_date' => '2026-03-10',
            'code' => 'V',
            'view_mode' => 'month',
            'month' => '2026-03',
        ]);

        $firstResponse->assertRedirect();
        $this->assertSame(1, $personnel->fresh()->pending_vacation_days);
        $this->assertSame('V', PersonnelCardexEntry::firstOrFail()->code);

        $secondResponse = $this->actingAs($admin)->post(route('cardex.store'), [
            'personnel_id' => $personnel->id,
            'entry_date' => '2026-03-10',
            'code' => 'A',
            'view_mode' => 'month',
            'month' => '2026-03',
        ]);

        $secondResponse->assertRedirect();
        $this->assertSame(2, $personnel->fresh()->pending_vacation_days);
        $this->assertSame('A', PersonnelCardexEntry::firstOrFail()->code);
    }

    public function test_vacation_code_requires_available_pending_days(): void
    {
        $admin = $this->adminUser();
        $personnel = Personnel::create([
            'employee_number' => 'RH-911',
            'first_name' => 'Alma',
            'last_name' => 'Nava',
            'pending_vacation_days' => 0,
            'active' => true,
        ]);

        $response = $this->from(route('cardex.index', ['personnel_id' => $personnel->id]))
            ->actingAs($admin)
            ->post(route('cardex.store'), [
                'personnel_id' => $personnel->id,
                'entry_date' => '2026-03-11',
                'code' => 'V',
                'view_mode' => 'month',
                'month' => '2026-03',
            ]);

        $response->assertRedirect(route('cardex.index', ['personnel_id' => $personnel->id]));
        $response->assertSessionHasErrors('code');
        $this->assertSame(0, $personnel->fresh()->pending_vacation_days);
        $this->assertCount(0, PersonnelCardexEntry::all());
    }

    private function adminUser(): User
    {
        return User::create([
            'name' => 'Admin RRHH',
            'username' => 'admin-cardex',
            'password' => 'secret',
            'role' => 'admin',
            'department' => 'rrhh',
            'active' => true,
        ]);
    }
}
