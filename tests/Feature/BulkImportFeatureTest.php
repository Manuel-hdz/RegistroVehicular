<?php

namespace Tests\Feature;

use App\Models\Part;
use App\Models\Personnel;
use App\Models\User;
use App\Models\Vehicle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class BulkImportFeatureTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_view_bulk_import_module(): void
    {
        $admin = $this->adminUser();

        $response = $this->actingAs($admin)->get(route('bulk-imports.index'));

        $response->assertOk();
        $response->assertSee('Cargas masivas');
        $response->assertSee('Personal');
        $response->assertSee('Vehiculos');
        $response->assertSee('Refacciones');
    }

    public function test_admin_can_download_personnel_template(): void
    {
        $admin = $this->adminUser();

        $response = $this->actingAs($admin)->get(route('bulk-imports.template', 'personnel'));

        $response->assertOk();
        $response->assertHeader('content-type', 'text/csv; charset=UTF-8');
        $response->assertSee('employee_number', false);
        $response->assertSee('first_name', false);
    }

    public function test_admin_can_import_personnel_records_from_csv(): void
    {
        $admin = $this->adminUser();

        $csv = implode("\n", [
            'employee_number,first_name,last_name,middle_name,curp,rfc,nss,department,position,marital_status,sex,birth_date,account_number,account_type,active,terminated_at',
            'RH-800,Ana,Lopez,Garcia,LOGA940305MZSABC01,LOGA-940305-AB1,123-45-67890,RRHH,Auxiliar,Casado(a),Femenino,1994-03-05,99887766,Nomina,si,',
        ]);

        $response = $this->actingAs($admin)->post(route('bulk-imports.store', 'personnel'), [
            'csv_file' => UploadedFile::fake()->createWithContent('personal.csv', $csv),
        ]);

        $response->assertRedirect(route('bulk-imports.index'));

        $personnel = Personnel::firstOrFail();
        $this->assertSame('RH-800', $personnel->employee_number);
        $this->assertSame('Ana', $personnel->first_name);
        $this->assertSame('LOGA940305MZSABC01', $personnel->curp);
        $this->assertSame('LOGA940305AB1', $personnel->rfc);
        $this->assertSame('1234567890', $personnel->nss);
        $this->assertSame('Casado(a)', $personnel->marital_status);
        $this->assertSame('Femenino', $personnel->sex);
        $this->assertSame('Nomina', $personnel->account_type);
    }

    public function test_admin_can_import_inactive_personnel_with_termination_date(): void
    {
        $admin = $this->adminUser();

        $csv = implode("\n", [
            'employee_number,first_name,last_name,active,terminated_at',
            'RH-802,Jose,Acevedo,no,2026-03-01',
        ]);

        $response = $this->actingAs($admin)->post(route('bulk-imports.store', 'personnel'), [
            'csv_file' => UploadedFile::fake()->createWithContent('personal.csv', $csv),
        ]);

        $response->assertRedirect(route('bulk-imports.index'));

        $personnel = Personnel::firstOrFail();
        $this->assertFalse($personnel->active);
        $this->assertSame('2026-03-01', optional($personnel->terminated_at)->format('Y-m-d'));
    }

    public function test_personnel_import_reports_friendly_error_when_a_field_is_too_long(): void
    {
        $admin = $this->adminUser();

        $csv = implode("\n", [
            'employee_number,first_name,last_name,account_type',
            'RH-801,Ana,Lopez,' . str_repeat('A', 60),
        ]);

        $response = $this->actingAs($admin)->post(route('bulk-imports.store', 'personnel'), [
            'csv_file' => UploadedFile::fake()->createWithContent('personal.csv', $csv),
        ]);

        $response->assertRedirect(route('bulk-imports.index'));
        $response->assertSessionHasErrors('csv_file');
        $response->assertSessionHas('import_errors');
        $this->assertCount(0, Personnel::all());
    }

    public function test_admin_can_import_vehicle_records_from_csv(): void
    {
        $admin = $this->adminUser();

        $csv = implode("\n", [
            'plate,identifier,vtype,model,year,serial_number,engine_number,supplier,assigned_personnel,description',
            'ZAC-123-A,EQ-01,pickup,Frontier,2022,SN-100,ENG-200,Proveedor Uno,Luis Perez,Unidad de trabajo',
        ]);

        $response = $this->actingAs($admin)->post(route('bulk-imports.store', 'vehicles'), [
            'csv_file' => UploadedFile::fake()->createWithContent('vehiculos.csv', $csv),
        ]);

        $response->assertRedirect(route('bulk-imports.index'));

        $vehicle = Vehicle::firstOrFail();
        $this->assertSame('ZAC-123-A', $vehicle->plate);
        $this->assertSame('EQ-01', $vehicle->identifier);
        $this->assertSame('pickup', $vehicle->vtype);
        $this->assertSame('Proveedor Uno', $vehicle->supplier);
    }

    public function test_admin_can_import_parts_records_from_csv(): void
    {
        $admin = $this->adminUser();

        $csv = implode("\n", [
            'name,unit_cost,active',
            'Filtro diesel,245.80,si',
        ]);

        $response = $this->actingAs($admin)->post(route('bulk-imports.store', 'parts'), [
            'csv_file' => UploadedFile::fake()->createWithContent('refacciones.csv', $csv),
        ]);

        $response->assertRedirect(route('bulk-imports.index'));

        $part = Part::firstOrFail();
        $this->assertSame('Filtro diesel', $part->name);
        $this->assertSame(245.8, (float) $part->unit_cost);
        $this->assertTrue($part->active);
    }

    private function adminUser(): User
    {
        return User::create([
            'name' => 'Administrador',
            'username' => 'admin-imports',
            'password' => 'secret',
            'role' => 'admin',
            'department' => 'administracion',
            'active' => true,
        ]);
    }
}
