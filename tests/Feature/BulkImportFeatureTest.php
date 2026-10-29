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
        $response->assertSee('Importar desde formato SISAD');
    }

    public function test_personnel_terminations_only_update_existing_employees(): void
    {
        $person = Personnel::create(['employee_number' => 'BA-1', 'first_name' => 'Ana', 'last_name' => 'Lopez', 'active' => true]);
        $other = Personnel::create(['employee_number' => 'BA-2', 'first_name' => 'Luis', 'last_name' => 'Perez', 'active' => true]);
        $response = $this->actingAs($this->adminUser())->post(route('bulk-imports.store', 'personnel-terminations'), [
            'csv_file' => UploadedFile::fake()->createWithContent('bajas.csv', "employee_number,terminated_at\nBA-1,2026-10-20\nNO-EXISTE,2026-10-20\nBA-2,fecha-invalida"),
        ]);
        $response->assertRedirect(route('bulk-imports.index'))->assertSessionHas('import_errors');
        $this->assertFalse($person->fresh()->active);
        $this->assertSame('2026-10-20', $person->fresh()->terminated_at->format('Y-m-d'));
        $this->assertSame('Ana', $person->fresh()->first_name);
        $this->assertTrue($other->fresh()->active);
        $this->assertSame(2, Personnel::count());
    }

    public function test_sisad_terminations_preserve_personnel_data_and_existing_termination_date(): void
    {
        $person = Personnel::create(['employee_number' => 'BA-3', 'first_name' => 'Ana', 'last_name' => 'Lopez', 'active' => false, 'terminated_at' => '2026-10-01']);
        $file = '<table><tr><td>ID EMPRESA</td><td>NOMBRE</td><td>APELLIDO PATERNO</td></tr><tr><td>BA-3</td><td>Otro</td><td>Nombre</td></tr></table>';
        $this->actingAs($this->adminUser())->post(route('bulk-imports.terminations.sisad.store'), [
            'sisad_file' => UploadedFile::fake()->createWithContent('bajas.xls', $file),
        ])->assertRedirect(route('bulk-imports.index'))->assertSessionHas('imported_type', 'personnel-terminations-sisad');
        $this->assertFalse($person->fresh()->active);
        $this->assertSame('Ana', $person->fresh()->first_name);
        $this->assertSame('2026-10-01', $person->fresh()->terminated_at->format('Y-m-d'));
    }

    public function test_sisad_terminations_create_missing_personnel_and_only_change_existing_status(): void
    {
        $admin = $this->adminUser();
        $existing = Personnel::create([
            'employee_number' => 'BA-4', 'first_name' => 'Original', 'last_name' => 'Registrado',
            'department' => 'Sistemas', 'active' => true, 'terminated_at' => null,
        ]);
        $other = Personnel::create(['employee_number' => 'BA-6', 'first_name' => 'Otro', 'last_name' => 'Empleado', 'active' => true]);
        $file = '<table><tr><td>ID EMPRESA</td><td>NOMBRE</td><td>APELLIDO PATERNO</td><td>APELLIDO MATERNO</td><td>PUESTO</td><td>FECHA INGRESO</td></tr>'
            .'<tr><td>BA-4</td><td>Cambiado</td><td>Distinto</td><td>Apellido</td><td>Otro puesto</td><td>fecha-invalida</td></tr>'
            .'<tr><td>BA-5</td><td>Nuevo</td><td>Empleado</td><td>Materno</td><td>Operador</td><td>2025-01-10</td></tr></table>';
        $this->actingAs($admin)->post(route('bulk-imports.terminations.sisad.store'), [
            'sisad_file' => UploadedFile::fake()->createWithContent('bajas.xls', $file),
        ])->assertRedirect(route('bulk-imports.index'))
            ->assertSessionHas('status', fn ($status) => str_contains($status, 'Nuevos inactivos: 1. Estatus actualizados: 1.'));
        $existing->refresh();
        $this->assertFalse($existing->active);
        $this->assertSame('Original', $existing->first_name);
        $this->assertSame('Sistemas', $existing->department);
        $this->assertNull($existing->terminated_at);
        $new = Personnel::where('employee_number', 'BA-5')->firstOrFail();
        $this->assertFalse($new->active);
        $this->assertSame('Nuevo', $new->first_name);
        $this->assertSame('Materno', $new->middle_name);
        $this->assertSame('Operador', $new->position);
        $this->assertSame('2025-01-10', $new->hire_date->format('Y-m-d'));
        $this->assertTrue($other->fresh()->active);
        // Repeating the same file must not create duplicate employees.
        $this->actingAs($admin)->post(route('bulk-imports.terminations.sisad.store'), [
            'sisad_file' => UploadedFile::fake()->createWithContent('bajas.xls', $file),
        ])->assertSessionHas('status', fn ($status) => str_contains($status, 'Nuevos inactivos: 0. Estatus actualizados: 2.'));
        $this->assertSame(3, Personnel::count());
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

    public function test_admin_can_import_personnel_from_sisad_xls_format(): void
    {
        $admin = $this->adminUser();
        $sisad = <<<'HTML'
        <html><body><table>
        <tr>
            <td>RFC</td><td>CURP</td><td>ID EMPRESA</td><td>NOMBRE</td>
            <td>APELLIDO PATERNO</td><td>APELLIDO MATERNO</td><td>NO. SEGURO SOCIAL</td>
            <td>FECHA INGRESO</td><td>PUESTO</td><td>NO. CUENTA</td><td>ÁREA</td>
            <td>CALLE</td><td>NÚMERO EXTERIOR</td><td>NÚMERO INTERIOR</td><td>COLONIA</td>
            <td>C.P.</td><td>LOCALIDAD</td><td>ESTADO</td><td>PAÍS</td><td>TELÉFONO</td>
            <td>FECHA DE NACIMIENTO</td><td>ESTADO CIVIL</td><td>CONTACTO POR ACCIDENTE</td>
        </tr>
        <tr>
            <td>LOGA940305AB1</td><td>LOGA940305MZSABC01</td><td>25870</td><td>ANA MARÍA</td>
            <td>LÓPEZ</td><td>GARCÍA</td><td>123-45-67890</td><td>2024-01-10</td>
            <td>AUXILIAR</td><td>0012345678</td><td>ADMINISTRACIÓN</td><td>CALLE NORTE</td>
            <td>14</td><td>N/D</td><td>CENTRO</td><td>99000</td><td>FRESNILLO</td>
            <td>ZACATECAS</td><td>MÉXICO</td><td>493-000-0000</td><td>05 de Marzo de 1994</td>
            <td>CASADA</td><td>Nombre: JUAN LÓPEZ<br>Telefono: 9331122<br>Celular: 4931112233</td>
        </tr>
        </table></body></html>
        HTML;
        $sisad = mb_convert_encoding($sisad, 'Windows-1252', 'UTF-8');

        $response = $this->actingAs($admin)->post(route('bulk-imports.sisad.store'), [
            'sisad_file' => UploadedFile::fake()->createWithContent('listaTrabajadores SISAD.xls', $sisad),
        ]);

        $response->assertRedirect(route('bulk-imports.index'));
        $response->assertSessionHas('imported_type', 'personnel-sisad');

        $personnel = Personnel::firstOrFail();
        $this->assertSame('25870', $personnel->employee_number);
        $this->assertSame('ANA MARÍA', $personnel->first_name);
        $this->assertSame('LÓPEZ', $personnel->last_name);
        $this->assertSame('Femenino', $personnel->sex);
        $this->assertSame('1994-03-05', $personnel->birth_date->format('Y-m-d'));
        $this->assertSame('2024-01-10', $personnel->hire_date->format('Y-m-d'));
        $this->assertSame('ADMINISTRACIÓN', $personnel->department);
        $this->assertSame('1234567890', $personnel->nss);
        $this->assertSame('4930000000', $personnel->phone);
        $this->assertSame('JUAN LÓPEZ', $personnel->emergency_contact_name);
        $this->assertSame('4931112233', $personnel->emergency_contact_phone);
        $this->assertStringContainsString('CALLE NORTE #14', $personnel->address);
        $this->assertStringContainsString('C.P. 99000', $personnel->address);
    }

    public function test_sisad_import_rejects_a_non_xls_file(): void
    {
        $admin = $this->adminUser();

        $this->from(route('bulk-imports.index'))->actingAs($admin)->post(route('bulk-imports.sisad.store'), [
            'sisad_file' => UploadedFile::fake()->createWithContent('personal.csv', 'contenido'),
        ])->assertRedirect(route('bulk-imports.index'))->assertSessionHasErrors('sisad_file');
    }

    public function test_sisad_import_recovers_a_malformed_birth_date_from_curp(): void
    {
        $admin = $this->adminUser();
        $sisad = '<table><tr><td>ID EMPRESA</td><td>NOMBRE</td><td>APELLIDO PATERNO</td><td>CURP</td><td>FECHA DE NACIMIENTO</td></tr>'
            .'<tr><td>25871</td><td>LUIS</td><td>PÉREZ</td><td>RAVR850728HZSMLM06</td><td>28 de Julio de 20A5</td></tr></table>';

        $response = $this->actingAs($admin)->post(route('bulk-imports.sisad.store'), [
            'sisad_file' => UploadedFile::fake()->createWithContent('personal-sisad.xls', $sisad),
        ]);

        $response->assertRedirect(route('bulk-imports.index'));
        $response->assertSessionHas('import_errors', []);
        $personnel = Personnel::where('employee_number', '25871')->firstOrFail();
        $this->assertSame('1985-07-28', $personnel->birth_date->format('Y-m-d'));
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
            'department' => 'sistemas',
            'active' => true,
        ]);
    }
}
