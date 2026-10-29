<?php

namespace App\Http\Controllers;

use App\Models\Part;
use App\Models\Personnel;
use App\Models\Vehicle;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class BulkImportController extends Controller
{
    public function index(): View
    {
        return view('admin.bulk-imports.index', [
            'imports' => $this->importDefinitions(),
        ]);
    }

    public function template(string $type): Response
    {
        $definition = $this->resolveImportDefinition($type);
        $rows = array_merge([$definition['headers']], $definition['examples']);

        return response("\xEF\xBB\xBF" . $this->buildCsv($rows), 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $definition['filename'] . '"',
        ]);
    }

    public function store(Request $request, string $type): RedirectResponse
    {
        $definition = $this->resolveImportDefinition($type);
        $resultRoute = $request->routeIs('vehicles.import.store')
            ? 'vehicles.index'
            : 'bulk-imports.index';

        $request->validate([
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
        ]);

        [$rows] = $this->readCsvRows($request->file('csv_file')->getRealPath());
        if (count($rows) === 0) {
            return back()->withErrors([
                'csv_file' => 'El archivo CSV esta vacio o no se pudo leer.',
            ]);
        }

        $headerMap = $this->detectHeaderMap($rows[0] ?? [], $definition['aliases']);
        $startIndex = $headerMap ? 1 : 0;
        if (!$headerMap) {
            $headerMap = $this->defaultHeaderMap($definition['headers']);
        }

        $createdCount = 0;
        $updatedCount = 0;
        $processedCount = 0;
        $errorMessages = [];
        $maxErrorsToShow = 80;

        foreach ($rows as $index => $row) {
            if ($index < $startIndex || $this->isEmptyRow($row)) {
                continue;
            }

            $lineNumber = $index + 1;
            try {
                $result = $this->processRow($type, $row, $headerMap, $lineNumber, $request);
            } catch (\Throwable $exception) {
                Log::error('Error al importar una fila CSV.', [
                    'type' => $type,
                    'line' => $lineNumber,
                    'exception' => $exception,
                ]);
                $this->appendError(
                    $errorMessages,
                    $maxErrorsToShow,
                    "Linea {$lineNumber}: no pudo importarse por un error inesperado."
                );
                continue;
            }

            if (($result['status'] ?? null) === 'created') {
                $createdCount++;
                $processedCount++;
                continue;
            }

            if (($result['status'] ?? null) === 'updated') {
                $updatedCount++;
                $processedCount++;
                continue;
            }

            if (!empty($result['error'])) {
                $this->appendError($errorMessages, $maxErrorsToShow, $result['error']);
            }
        }

        if ($processedCount === 0) {
            return redirect()->route($resultRoute)
                ->withErrors([
                    'csv_file' => 'No se importo ningun registro valido.',
                ])
                ->with('imported_type', $type)
                ->with('import_errors', $errorMessages);
        }

        $status = "Importacion de {$definition['label']} completada. Nuevos: {$createdCount}. Actualizados: {$updatedCount}.";
        if ($type === 'personnel-terminations') {
            $status = "Bajas de personal completadas. Registros procesados: {$updatedCount}.";
        }
        if (count($errorMessages) > 0) {
            $status .= ' Se omitieron algunas lineas con error.';
        }

        return redirect()->route($resultRoute)
            ->with('status', $status)
            ->with('imported_type', $type)
            ->with('import_errors', $errorMessages);
    }

    public function storeSisad(Request $request, bool $terminate = false): RedirectResponse
    {
        $request->validate([
            'sisad_file' => ['required', 'file', 'max:10240'],
        ]);

        $file = $request->file('sisad_file');
        if (Str::lower($file->getClientOriginalExtension()) !== 'xls') {
            throw ValidationException::withMessages([
                'sisad_file' => 'Selecciona el archivo .xls generado por SISAD.',
            ]);
        }

        [$rows, $sisadHeaderMap] = $this->readSisadRows($file->getRealPath());
        if ($rows === [] || $sisadHeaderMap === []) {
            return back()->withErrors([
                'sisad_file' => 'El archivo no contiene la tabla de personal esperada del formato SISAD.',
            ]);
        }

        $definition = $this->resolveImportDefinition('personnel');
        $importedType = $terminate ? 'personnel-terminations-sisad' : 'personnel-sisad';
        $personnelHeaderMap = $this->defaultHeaderMap($definition['headers']);
        $createdCount = 0;
        $updatedCount = 0;
        $processedCount = 0;
        $errorMessages = [];
        $maxErrorsToShow = 80;

        foreach ($rows as $index => $sisadRow) {
            if ($this->isEmptyRow($sisadRow)) {
                continue;
            }

            $lineNumber = $index + 2;
            $row = $this->mapSisadPersonnelRow($sisadRow, $sisadHeaderMap, $definition['headers']);
            foreach (['birth_date' => 'fecha de nacimiento', 'hire_date' => 'fecha de ingreso'] as $field => $label) {
                $fieldIndex = $personnelHeaderMap[$field];
                $value = trim($row[$fieldIndex] ?? '');
                if ($value !== '' && $this->nullableDate($value) === null) {
                    $this->appendError(
                        $errorMessages,
                        $maxErrorsToShow,
                        "Fila SISAD {$lineNumber}: la {$label} no es válida; el registro se importó sin ese dato."
                    );
                    $row[$fieldIndex] = '';
                }
            }

            try {
                $result = $terminate
                    ? $this->processSisadTerminationRow($row, $personnelHeaderMap, $lineNumber)
                    : $this->processPersonnelRow($row, $personnelHeaderMap, $lineNumber);
            } catch (\Throwable $exception) {
                Log::error('Error al importar una fila SISAD.', [
                    'line' => $lineNumber,
                    'exception' => $exception,
                ]);
                $this->appendError(
                    $errorMessages,
                    $maxErrorsToShow,
                    "Fila SISAD {$lineNumber}: no pudo importarse por un error inesperado."
                );
                continue;
            }

            if (($result['status'] ?? null) === 'created') {
                $createdCount++;
                $processedCount++;
            } elseif (($result['status'] ?? null) === 'updated') {
                $updatedCount++;
                $processedCount++;
            } elseif (! empty($result['error'])) {
                $this->appendError($errorMessages, $maxErrorsToShow, $result['error']);
            }
        }

        if ($processedCount === 0) {
            return redirect()->route('bulk-imports.index')
                ->withErrors(['sisad_file' => 'No se importó ningún registro válido del archivo SISAD.'])
                ->with('imported_type', $importedType)
                ->with('import_errors', $errorMessages);
        }

        $status = "Importación SISAD de Personal completada. Nuevos: {$createdCount}. Actualizados: {$updatedCount}.";
        if ($terminate) {
            $status = "Bajas de personal SISAD completadas. Nuevos inactivos: {$createdCount}. Estatus actualizados: {$updatedCount}.";
        }
        if ($errorMessages !== []) {
            $status .= ' Se detectaron filas con advertencias o errores.';
        }

        return redirect()->route('bulk-imports.index')
            ->with('status', $status)
            ->with('imported_type', $importedType)
            ->with('import_errors', $errorMessages);
    }

    /**
     * @return array<int, array{key:string,label:string,description:string,filename:string,headers:array<int,string>,examples:array<int,array<int,string>>,aliases:array<string,array<int,string>>,notes:array<int,string>}>
     */
    public function storeSisadTerminations(Request $request): RedirectResponse
    {
        return $this->storeSisad($request, true);
    }

    private function processSisadTerminationRow(array $row, array $headerMap, int $lineNumber): array
    {
        $number = $this->rowValue($row, $headerMap, 'employee_number');
        if ($number !== '') {
            $personnel = Personnel::where('employee_number', $number)->first();
            if ($personnel) {
                $personnel->update(['active' => false]);
                return ['status' => 'updated'];
            }
        }

        // Reuse the personnel validation and mapping, forcing new records inactive.
        $row[$headerMap['active']] = 'no';
        return $this->processPersonnelRow($row, $headerMap, $lineNumber);
    }

    private function processTerminationRow(array $row, array $headerMap, int $lineNumber): array
    {
        $number = $this->rowValue($row, $headerMap, 'employee_number');
        if ($number === '') {
            return ['status' => 'skipped', 'error' => "Linea {$lineNumber}: falta el numero de empleado."];
        }
        $personnel = Personnel::where('employee_number', $number)->first();
        if (!$personnel) {
            return ['status' => 'skipped', 'error' => "Linea {$lineNumber}: no existe el empleado {$number}."];
        }
        $value = $this->rowValue($row, $headerMap, 'terminated_at');
        $date = $value === '' ? null : $this->nullableDate($this->normalizeSisadDate($value));
        if ($value !== '' && $date === null) {
            return ['status' => 'skipped', 'error' => "Linea {$lineNumber}: fecha de baja invalida."];
        }
        $personnel->update([
            'active' => false,
            'terminated_at' => $date ?: ($personnel->terminated_at ?: now()->toDateString()),
        ]);
        return ['status' => 'updated'];
    }

    private function importDefinitions(): array
    {
        return [
            [
                'key' => 'personnel-terminations',
                'label' => 'Bajas de personal',
                'description' => 'Da de baja al personal incluido en el archivo, sin modificar sus demas datos.',
                'filename' => 'plantilla_bajas_personal.csv',
                'headers' => ['employee_number', 'terminated_at'],
                'examples' => [['RH-001', '2026-10-21']],
                'aliases' => [
                    'employee_number' => ['employee_number', 'numero_empleado', 'no_empleado', 'num_empleado', 'id_empresa'],
                    'terminated_at' => ['terminated_at', 'fecha_baja', 'fecha_de_baja', 'baja'],
                ],
                'notes' => [
                    'Con plantilla CSV, el numero de empleado es obligatorio y debe existir en el sistema.',
                    'Con SISAD, los empleados no registrados se crean como inactivos; a los existentes solo se les cambia el estatus.',
                    'Carga solo los empleados que deseas dar de baja. Todos los incluidos se marcaran como inactivos.',
                    'La fecha de baja es opcional: se conserva la existente o se usa la fecha de hoy.',
                    'Los empleados ausentes del archivo no se modifican.',
                ],
            ],
            [
                'key' => 'personnel',
                'label' => 'Personal',
                'description' => 'Carga colaboradores con sus datos base de RRHH. Se actualiza por numero de empleado.',
                'filename' => 'plantilla_personal.csv',
                'headers' => [
                    'employee_number',
                    'first_name',
                    'last_name',
                    'middle_name',
                    'curp',
                    'rfc',
                    'nss',
                    'marital_status',
                    'sex',
                    'birth_date',
                    'department',
                    'position',
                    'hire_date',
                    'account_number',
                    'account_type',
                    'phone',
                    'email',
                    'address',
                    'emergency_contact_name',
                    'emergency_contact_phone',
                    'active',
                    'terminated_at',
                ],
                'examples' => [
                    ['RH-001', 'Laura', 'Lopez', 'Garcia', 'LOGL920814MZSABC01', 'LOGL920814AB1', '9988776655', 'Casado(a)', 'Femenino', '1992-08-14', 'Administracion', 'Auxiliar', '2024-01-10', '1234567890', 'Nomina', '4930000000', 'laura@example.com', 'Calle Norte 14', 'Juan Lopez', '4931112233', 'si', ''],
                    ['RH-002', 'Carlos', 'Perez', 'Soto', '', '', '', 'Soltero(a)', 'Masculino', '1989-05-22', 'Mantenimiento', 'Mecanico', '2023-07-01', '', '', '4932223344', 'carlos@example.com', 'Colonia Centro', 'Ana Perez', '4934445566', 'no', '2026-03-01'],
                ],
                'aliases' => [
                    'employee_number' => ['employee_number', 'numero_empleado', 'no_empleado', 'num_empleado'],
                    'first_name' => ['first_name', 'nombre', 'nombres'],
                    'last_name' => ['last_name', 'apellido_paterno', 'paterno'],
                    'middle_name' => ['middle_name', 'apellido_materno', 'materno'],
                    'curp' => ['curp'],
                    'rfc' => ['rfc'],
                    'nss' => ['nss', 'seguro_social'],
                    'marital_status' => ['marital_status', 'estado_civil'],
                    'sex' => ['sex', 'sexo'],
                    'birth_date' => ['birth_date', 'fecha_nacimiento', 'nacimiento'],
                    'department' => ['department', 'departamento', 'area'],
                    'position' => ['position', 'puesto', 'cargo'],
                    'hire_date' => ['hire_date', 'fecha_ingreso', 'ingreso'],
                    'account_number' => ['account_number', 'numero_cuenta', 'cuenta'],
                    'account_type' => ['account_type', 'tipo_cuenta'],
                    'phone' => ['phone', 'telefono', 'celular'],
                    'email' => ['email', 'correo', 'correo_electronico'],
                    'address' => ['address', 'domicilio', 'direccion'],
                    'emergency_contact_name' => ['emergency_contact_name', 'contacto_emergencia'],
                    'emergency_contact_phone' => ['emergency_contact_phone', 'telefono_emergencia'],
                    'active' => ['active', 'activo', 'estatus'],
                    'terminated_at' => ['terminated_at', 'fecha_baja', 'baja', 'fecha_de_baja'],
                ],
                'notes' => [
                    'Campos obligatorios: employee_number, first_name y last_name.',
                    'Las fechas aceptan formatos como 2026-03-18, 18/03/2026 o 03/18/2026.',
                    'Si active viene en no o inactivo, puedes indicar terminated_at para registrar la fecha de baja.',
                    'No incluye fotografias; esas se siguen cargando manualmente.',
                ],
            ],
            [
                'key' => 'vehicles',
                'label' => 'Vehiculos',
                'description' => 'Carga equipos y unidades. Se actualiza por clave, placa o numero de serie.',
                'filename' => 'plantilla_vehiculos.csv',
                'headers' => [
                    'key', 'unit_name', 'registration_date', 'make_model', 'model', 'plate',
                    'serial_number', 'additional_serial_number', 'engine_type', 'engine_filters',
                    'area', 'family', 'manufacture_date', 'assigned_to', 'status', 'supplier',
                ],
                'examples' => [
                    ['EQ-01', 'Excavadora principal', '2026-01-15', 'Caterpillar 320D', '2024', '', 'SER-100', 'SER-ALT-1', 'Diesel', 'Aceite; combustible; aire', 'Obra', 'Maquinaria pesada', '2024-05-10', 'Luis Torres', 'Operando', 'Caterpillar'],
                ],
                'aliases' => [
                    'identifier' => ['key', 'clave', 'identifier', 'identificador'],
                    'unit_name' => ['unit_name', 'nombre_unidad', 'nombre_de_unidad'],
                    'registration_date' => ['registration_date', 'fecha_alta', 'fecha_de_alta'],
                    'make_model' => ['make_model', 'marca_modelo', 'marca/modelo'],
                    'model' => ['model', 'modelo'],
                    'plate' => ['plate', 'placa'],
                    'serial_number' => ['serial_number', 'numero_serie', 'numero_de_serie', 'serie'],
                    'additional_serial_number' => ['additional_serial_number', 'serie_eq_adicional', 'serie_adicional'],
                    'engine_type' => ['engine_type', 'tipo_motor', 'tipo_de_motor'],
                    'engine_filters' => ['engine_filters', 'filtros_motor', 'filtros_de_motor'],
                    'area' => ['area'],
                    'family' => ['family', 'familia'],
                    'manufacture_date' => ['manufacture_date', 'fecha_fabricacion', 'fecha_de_fabricacion'],
                    'assigned_personnel' => ['assigned_to', 'asignado', 'assigned_personnel', 'personal_asignado'],
                    'equipment_status' => ['status', 'estado', 'estatus'],
                    'supplier' => ['supplier', 'proveedor'],
                ],
                'notes' => [
                    'Todos los campos son opcionales.',
                    'Si existe clave, placa o numero de serie se actualiza el equipo coincidente; de lo contrario se crea uno nuevo.',
                    'Tenencia y tarjeta de circulacion son archivos y se adjuntan desde la edicion del equipo.',
                ],
            ],            [
                'key' => 'parts',
                'label' => 'Refacciones',
                'description' => 'Carga materiales y refacciones. Se actualiza por nombre.',
                'filename' => 'plantilla_refacciones.csv',
                'headers' => [
                    'name',
                    'unit_cost',
                    'active',
                ],
                'examples' => [
                    ['Filtro de aceite', '185.50', 'si'],
                    ['Balata delantera', '960.00', 'si'],
                ],
                'aliases' => [
                    'name' => ['name', 'nombre'],
                    'unit_cost' => ['unit_cost', 'costo_unitario', 'precio_unitario', 'costo'],
                    'active' => ['active', 'activo', 'estatus'],
                ],
                'notes' => [
                    'Campos obligatorios: name y unit_cost.',
                    'unit_cost debe ser numerico y puede usar decimales.',
                ],
            ],
        ];
    }

    /**
     * @return array{key:string,label:string,description:string,filename:string,headers:array<int,string>,examples:array<int,array<int,string>>,aliases:array<string,array<int,string>>,notes:array<int,string>}
     */
    private function resolveImportDefinition(string $type): array
    {
        foreach ($this->importDefinitions() as $definition) {
            if ($definition['key'] === $type) {
                return $definition;
            }
        }

        abort(404);
    }

    /**
     * @param array<int, string> $row
     * @param array<string, int> $headerMap
     * @return array{status:string,error?:string}
     */
    private function processRow(string $type, array $row, array $headerMap, int $lineNumber, Request $request): array
    {
        return match ($type) {
            'personnel-terminations' => $this->processTerminationRow($row, $headerMap, $lineNumber),
            'personnel' => $this->processPersonnelRow($row, $headerMap, $lineNumber),
            'vehicles' => $this->processVehicleRow($row, $headerMap, $lineNumber, $request),
            'parts' => $this->processPartRow($row, $headerMap, $lineNumber),
            default => ['status' => 'skipped', 'error' => "Linea {$lineNumber}: tipo de importacion no soportado."],
        };
    }

    /**
     * @param array<int, string> $row
     * @param array<string, int> $headerMap
     * @return array{status:string,error?:string}
     */
    private function processPersonnelRow(array $row, array $headerMap, int $lineNumber): array
    {
        $employeeNumber = $this->rowValue($row, $headerMap, 'employee_number');
        $firstName = $this->rowValue($row, $headerMap, 'first_name');
        $lastName = $this->rowValue($row, $headerMap, 'last_name');
        $curp = $this->sanitizeUpperAlnum($this->rowValue($row, $headerMap, 'curp'));
        $rfc = $this->sanitizeUpperAlnum($this->rowValue($row, $headerMap, 'rfc'));
        $nss = $this->sanitizeDigits($this->rowValue($row, $headerMap, 'nss'));

        if ($employeeNumber === '' || $firstName === '' || $lastName === '') {
            return ['status' => 'skipped', 'error' => "Linea {$lineNumber}: faltan employee_number, first_name o last_name."];
        }

        foreach ([
            'employee_number' => [$employeeNumber, 50],
            'first_name' => [$firstName, 120],
            'last_name' => [$lastName, 120],
            'middle_name' => [$this->rowValue($row, $headerMap, 'middle_name'), 120],
            'curp' => [$curp, 18],
            'rfc' => [$rfc, 13],
            'nss' => [$nss, 20],
            'marital_status' => [$this->rowValue($row, $headerMap, 'marital_status'), 50],
            'sex' => [$this->rowValue($row, $headerMap, 'sex'), 20],
            'department' => [$this->rowValue($row, $headerMap, 'department'), 120],
            'position' => [$this->rowValue($row, $headerMap, 'position'), 120],
            'account_number' => [$this->rowValue($row, $headerMap, 'account_number'), 50],
            'account_type' => [$this->rowValue($row, $headerMap, 'account_type'), 50],
            'phone' => [$this->rowValue($row, $headerMap, 'phone'), 40],
            'email' => [$this->rowValue($row, $headerMap, 'email'), 150],
            'address' => [$this->rowValue($row, $headerMap, 'address'), 255],
            'emergency_contact_name' => [$this->rowValue($row, $headerMap, 'emergency_contact_name'), 150],
            'emergency_contact_phone' => [$this->rowValue($row, $headerMap, 'emergency_contact_phone'), 40],
        ] as $field => [$value, $maxLength]) {
            if (!$this->fitsMaxLength((string) $value, $maxLength)) {
                return ['status' => 'skipped', 'error' => "Linea {$lineNumber}: el campo {$field} excede {$maxLength} caracteres."];
            }
        }

        $birthDate = $this->nullableDate($this->rowValue($row, $headerMap, 'birth_date'));
        if ($this->rowValue($row, $headerMap, 'birth_date') !== '' && $birthDate === null) {
            return ['status' => 'skipped', 'error' => "Linea {$lineNumber}: la fecha de nacimiento no es valida."];
        }

        $hireDate = $this->nullableDate($this->rowValue($row, $headerMap, 'hire_date'));
        if ($this->rowValue($row, $headerMap, 'hire_date') !== '' && $hireDate === null) {
            return ['status' => 'skipped', 'error' => "Linea {$lineNumber}: la fecha de ingreso no es valida."];
        }

        $terminatedAt = $this->nullableDate($this->rowValue($row, $headerMap, 'terminated_at'));
        if ($this->rowValue($row, $headerMap, 'terminated_at') !== '' && $terminatedAt === null) {
            return ['status' => 'skipped', 'error' => "Linea {$lineNumber}: la fecha de baja no es valida."];
        }

        $isActive = $this->parseBoolean($this->rowValue($row, $headerMap, 'active'), true);

        $personnel = Personnel::firstOrNew([
            'employee_number' => $employeeNumber,
        ]);
        $isNew = !$personnel->exists;

        $personnel->fill([
            'first_name' => $firstName,
            'last_name' => $lastName,
            'middle_name' => $this->nullableString($this->rowValue($row, $headerMap, 'middle_name')),
            'curp' => $this->nullableString($curp),
            'rfc' => $this->nullableString($rfc),
            'nss' => $this->nullableString($nss),
            'marital_status' => $this->nullableString($this->rowValue($row, $headerMap, 'marital_status')),
            'sex' => $this->nullableString($this->rowValue($row, $headerMap, 'sex')),
            'birth_date' => $birthDate,
            'department' => $this->nullableString($this->rowValue($row, $headerMap, 'department')),
            'position' => $this->nullableString($this->rowValue($row, $headerMap, 'position')),
            'hire_date' => $hireDate,
            'account_number' => $this->nullableString($this->rowValue($row, $headerMap, 'account_number')),
            'account_type' => $this->nullableString($this->rowValue($row, $headerMap, 'account_type')),
            'phone' => $this->nullableString($this->rowValue($row, $headerMap, 'phone')),
            'email' => $this->nullableString($this->rowValue($row, $headerMap, 'email')),
            'address' => $this->nullableString($this->rowValue($row, $headerMap, 'address')),
            'emergency_contact_name' => $this->nullableString($this->rowValue($row, $headerMap, 'emergency_contact_name')),
            'emergency_contact_phone' => $this->nullableString($this->rowValue($row, $headerMap, 'emergency_contact_phone')),
            'active' => $isActive,
        ]);

        if ($personnel->active) {
            $personnel->terminated_at = null;
        } else {
            $personnel->terminated_at = $terminatedAt ?: ($personnel->terminated_at ?: now()->toDateString());
        }

        $personnel->save();

        return ['status' => $isNew ? 'created' : 'updated'];
    }

    /**
     * @param array<int, string> $row
     * @param array<string, int> $headerMap
     * @return array{status:string,error?:string}
     */
    private function processVehicleRow(array $row, array $headerMap, int $lineNumber, Request $request): array
    {
        $identifier = $this->rowValue($row, $headerMap, 'identifier');
        $plate = $this->rowValue($row, $headerMap, 'plate');
        $serialNumber = $this->rowValue($row, $headerMap, 'serial_number');

        $registrationDateValue = $this->rowValue($row, $headerMap, 'registration_date');
        $registrationDate = $this->nullableDate($registrationDateValue);

        $manufactureDateValue = $this->rowValue($row, $headerMap, 'manufacture_date');
        $manufactureDate = $this->nullableDate($manufactureDateValue);

        $vehicle = null;
        foreach ([
            'identifier' => $identifier,
            'plate' => $plate,
            'serial_number' => $serialNumber,
        ] as $field => $value) {
            if ($value === '') {
                continue;
            }

            $vehicle = Vehicle::where($field, $value)->first();
            if ($vehicle) {
                break;
            }
        }

        $vehicle ??= new Vehicle();

        $isNew = !$vehicle->exists;
        $vehicle->fill([
            'identifier' => $this->nullableString($identifier),
            'unit_name' => $this->nullableString($this->rowValue($row, $headerMap, 'unit_name')),
            'registration_date' => $registrationDate,
            'make_model' => $this->nullableString($this->rowValue($row, $headerMap, 'make_model')),
            'model' => $this->nullableString($this->rowValue($row, $headerMap, 'model')),
            'plate' => $this->nullableString($plate),
            'serial_number' => $this->nullableString($serialNumber),
            'additional_serial_number' => $this->nullableString($this->rowValue($row, $headerMap, 'additional_serial_number')),
            'engine_type' => $this->nullableString($this->rowValue($row, $headerMap, 'engine_type')),
            'engine_filters' => $this->nullableString($this->rowValue($row, $headerMap, 'engine_filters')),
            'area' => $this->nullableString($this->rowValue($row, $headerMap, 'area')),
            'family' => $this->nullableString($this->rowValue($row, $headerMap, 'family')),
            'manufacture_date' => $manufactureDate,
            'assigned_personnel' => $this->nullableString($this->rowValue($row, $headerMap, 'assigned_personnel')),
            'equipment_status' => $this->nullableString($this->rowValue($row, $headerMap, 'equipment_status')),
            'supplier' => $this->nullableString($this->rowValue($row, $headerMap, 'supplier')),
            'active' => $vehicle->exists ? (bool) $vehicle->active : true,
        ]);
        $vehicle->save();

        return ['status' => $isNew ? 'created' : 'updated'];
    }
    /**
     * @param array<int, string> $row
     * @param array<string, int> $headerMap
     * @return array{status:string,error?:string}
     */
    private function processPartRow(array $row, array $headerMap, int $lineNumber): array
    {
        $name = $this->rowValue($row, $headerMap, 'name');
        $unitCostValue = $this->rowValue($row, $headerMap, 'unit_cost');

        if ($name === '' || $unitCostValue === '') {
            return ['status' => 'skipped', 'error' => "Linea {$lineNumber}: faltan name o unit_cost."];
        }

        $unitCost = $this->nullableDecimal($unitCostValue);
        if ($unitCost === null || $unitCost < 0) {
            return ['status' => 'skipped', 'error' => "Linea {$lineNumber}: el costo '{$unitCostValue}' no es valido."];
        }

        $part = Part::firstOrNew([
            'name' => $name,
        ]);
        $isNew = !$part->exists;

        $part->fill([
            'name' => $name,
            'unit_cost' => $unitCost,
            'active' => $this->parseBoolean($this->rowValue($row, $headerMap, 'active'), true),
        ]);
        $part->save();

        return ['status' => $isNew ? 'created' : 'updated'];
    }

    /**
     * @return array{0: array<int, array<int, string>>, 1: array<string, int>}
     */
    private function readSisadRows(string $filePath): array
    {
        $contents = file_get_contents($filePath);
        if ($contents === false || stripos($contents, '<table') === false) {
            return [[], []];
        }

        $encoding = mb_detect_encoding($contents, ['UTF-8', 'Windows-1252', 'ISO-8859-1'], true) ?: 'Windows-1252';
        if ($encoding !== 'UTF-8') {
            $contents = mb_convert_encoding($contents, 'UTF-8', $encoding);
        }

        $previousUseErrors = libxml_use_internal_errors(true);
        $document = new \DOMDocument();
        $loaded = $document->loadHTML(
            '<?xml encoding="UTF-8">'.$contents,
            LIBXML_NONET | LIBXML_NOERROR | LIBXML_NOWARNING | LIBXML_COMPACT
        );
        libxml_clear_errors();
        libxml_use_internal_errors($previousUseErrors);

        if (! $loaded) {
            return [[], []];
        }

        $rows = [];
        $headerMap = [];
        foreach ($document->getElementsByTagName('tr') as $tableRow) {
            $cells = [];
            foreach ($tableRow->childNodes as $child) {
                if (! $child instanceof \DOMElement || ! in_array(Str::lower($child->tagName), ['td', 'th'], true)) {
                    continue;
                }

                $cells[] = $this->sisadCellText($document, $child);
            }

            if ($cells === []) {
                continue;
            }

            if ($headerMap === []) {
                $candidateMap = $this->detectHeaderMap($cells, $this->sisadHeaderAliases()) ?? [];
                if ($this->hasRequiredSisadHeaders($candidateMap)) {
                    $headerMap = $candidateMap;
                }
                continue;
            }

            $rows[] = $cells;
        }

        return [$rows, $headerMap];
    }

    private function sisadCellText(\DOMDocument $document, \DOMElement $cell): string
    {
        $html = $document->saveHTML($cell) ?: '';
        $html = preg_replace('/<br\s*\/?>/i', "\n", $html) ?? $html;
        $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = str_replace("\u{00A0}", ' ', $text);
        $text = preg_replace('/[ \t]+/u', ' ', $text) ?? $text;
        $text = preg_replace('/\R+/u', "\n", $text) ?? $text;

        return trim($text);
    }

    /** @return array<string, array<int, string>> */
    private function sisadHeaderAliases(): array
    {
        return [
            'rfc' => ['rfc'],
            'curp' => ['curp'],
            'employee_number' => ['id_empresa'],
            'terminated_at' => ['fecha_baja', 'fecha_de_baja', 'baja'],
            'first_name' => ['nombre'],
            'last_name' => ['apellido_paterno'],
            'middle_name' => ['apellido_materno'],
            'nss' => ['no_seguro_social'],
            'hire_date' => ['fecha_ingreso'],
            'position' => ['puesto'],
            'account_number' => ['no_cuenta'],
            'department' => ['area'],
            'street' => ['calle'],
            'exterior_number' => ['numero_exterior'],
            'interior_number' => ['numero_interior'],
            'neighborhood' => ['colonia'],
            'postal_code' => ['c_p'],
            'locality' => ['localidad'],
            'state' => ['estado'],
            'country' => ['pais'],
            'phone' => ['telefono'],
            'birth_date' => ['fecha_de_nacimiento'],
            'marital_status' => ['estado_civil'],
            'emergency_contact' => ['contacto_por_accidente'],
        ];
    }

    /** @param array<string, int> $headerMap */
    private function hasRequiredSisadHeaders(array $headerMap): bool
    {
        foreach (['employee_number', 'first_name', 'last_name'] as $requiredField) {
            if (! array_key_exists($requiredField, $headerMap)) {
                return false;
            }
        }

        return true;
    }

    /**
     * @param array<int, string> $row
     * @param array<string, int> $sisadHeaderMap
     * @param array<int, string> $personnelHeaders
     * @return array<int, string>
     */
    private function mapSisadPersonnelRow(array $row, array $sisadHeaderMap, array $personnelHeaders): array
    {
        $curp = $this->rowValue($row, $sisadHeaderMap, 'curp');
        $birthDate = $this->normalizeSisadDate($this->rowValue($row, $sisadHeaderMap, 'birth_date'));
        if ($this->nullableDate($birthDate) === null) {
            $birthDate = $this->birthDateFromCurp($curp);
        }
        [$emergencyName, $emergencyPhone] = $this->parseSisadEmergencyContact(
            $this->rowValue($row, $sisadHeaderMap, 'emergency_contact')
        );

        $values = [
            'employee_number' => $this->rowValue($row, $sisadHeaderMap, 'employee_number'),
            'first_name' => $this->rowValue($row, $sisadHeaderMap, 'first_name'),
            'last_name' => $this->rowValue($row, $sisadHeaderMap, 'last_name'),
            'middle_name' => $this->rowValue($row, $sisadHeaderMap, 'middle_name'),
            'curp' => $curp,
            'rfc' => $this->rowValue($row, $sisadHeaderMap, 'rfc'),
            'nss' => $this->rowValue($row, $sisadHeaderMap, 'nss'),
            'marital_status' => $this->rowValue($row, $sisadHeaderMap, 'marital_status'),
            'sex' => $this->sexFromCurp($curp),
            'birth_date' => $birthDate,
            'department' => $this->rowValue($row, $sisadHeaderMap, 'department'),
            'position' => $this->rowValue($row, $sisadHeaderMap, 'position'),
            'hire_date' => $this->rowValue($row, $sisadHeaderMap, 'hire_date'),
            'account_number' => $this->rowValue($row, $sisadHeaderMap, 'account_number'),
            'phone' => $this->sanitizeDigits($this->rowValue($row, $sisadHeaderMap, 'phone')),
            'address' => $this->composeSisadAddress($row, $sisadHeaderMap),
            'emergency_contact_name' => $emergencyName,
            'emergency_contact_phone' => $emergencyPhone,
            'active' => 'si',
        ];

        return array_map(
            static fn (string $field): string => (string) ($values[$field] ?? ''),
            $personnelHeaders
        );
    }

    private function sexFromCurp(string $curp): string
    {
        $curp = $this->sanitizeUpperAlnum($curp);
        $sexCode = strlen($curp) >= 11 ? $curp[10] : '';

        return match ($sexCode) {
            'H' => 'Masculino',
            'M' => 'Femenino',
            default => '',
        };
    }

    private function birthDateFromCurp(string $curp): string
    {
        $curp = $this->sanitizeUpperAlnum($curp);
        if (strlen($curp) < 10 || ! preg_match('/^(\d{2})(\d{2})(\d{2})$/', substr($curp, 4, 6), $matches)) {
            return '';
        }

        $shortYear = (int) $matches[1];
        $year = $shortYear <= ((int) now()->format('y')) ? 2000 + $shortYear : 1900 + $shortYear;
        $month = (int) $matches[2];
        $day = (int) $matches[3];

        if (! checkdate($month, $day, $year)) {
            return '';
        }

        return sprintf('%04d-%02d-%02d', $year, $month, $day);
    }

    private function normalizeSisadDate(string $value): string
    {
        $value = trim($value);
        if ($value === '') {
            return '';
        }

        $months = [
            'enero' => '01', 'febrero' => '02', 'marzo' => '03', 'abril' => '04',
            'mayo' => '05', 'junio' => '06', 'julio' => '07', 'agosto' => '08',
            'septiembre' => '09', 'setiembre' => '09', 'octubre' => '10',
            'noviembre' => '11', 'diciembre' => '12',
        ];
        $normalized = Str::lower(Str::ascii($value));

        if (preg_match('/^(\d{1,2})\s+de\s+([a-z]+)\s+de\s+(\d{4})$/', $normalized, $matches)) {
            $month = $months[$matches[2]] ?? null;
            if ($month !== null) {
                return sprintf('%04d-%s-%02d', (int) $matches[3], $month, (int) $matches[1]);
            }
        }

        return $value;
    }

    /** @param array<int, string> $row @param array<string, int> $headerMap */
    private function composeSisadAddress(array $row, array $headerMap): string
    {
        $street = $this->sisadMeaningfulValue($this->rowValue($row, $headerMap, 'street'));
        $exterior = $this->sisadMeaningfulValue($this->rowValue($row, $headerMap, 'exterior_number'));
        $interior = $this->sisadMeaningfulValue($this->rowValue($row, $headerMap, 'interior_number'));
        $parts = [];
        $streetLine = trim(implode(' ', array_filter([
            $street,
            $exterior !== '' ? '#'.$exterior : '',
            $interior !== '' ? 'Int. '.$interior : '',
        ])));
        if ($streetLine !== '') {
            $parts[] = $streetLine;
        }

        foreach (['neighborhood' => 'Col. ', 'postal_code' => 'C.P. ', 'locality' => '', 'state' => '', 'country' => ''] as $field => $prefix) {
            $value = $this->sisadMeaningfulValue($this->rowValue($row, $headerMap, $field));
            if ($value !== '') {
                $parts[] = $prefix.$value;
            }
        }

        return implode(', ', $parts);
    }

    private function sisadMeaningfulValue(string $value): string
    {
        $value = trim($value);

        return in_array($this->normalizeToken($value), ['', 'n_a', 'n_d', 'na', 'nd'], true) ? '' : $value;
    }

    /** @return array{0: string, 1: string} */
    private function parseSisadEmergencyContact(string $value): array
    {
        $name = '';
        $phone = '';

        if (preg_match('/Nombre:\s*(.*?)(?:\R|Telefono:|Celular:|$)/iu', $value, $matches)) {
            $name = trim($matches[1]);
        }
        if (preg_match('/Celular:\s*([^\r\n]*)/iu', $value, $matches)) {
            $phone = $this->sanitizeDigits($matches[1]);
        }
        if ($phone === '' && preg_match('/Telefono:\s*([^\r\n]*)/iu', $value, $matches)) {
            $phone = $this->sanitizeDigits($matches[1]);
        }

        return [$name, $phone];
    }

    /** @param array<int, array<int, string>> $rows */
    private function buildCsv(array $rows): string
    {
        $csv = '';
        foreach ($rows as $row) {
            $escaped = array_map(function (string $value): string {
                $value = str_replace('"', '""', $value);
                return '"' . $value . '"';
            }, $row);

            $csv .= implode(',', $escaped) . "\r\n";
        }

        return $csv;
    }

    /**
     * @return array{0: array<int, array<int, string>>, 1: string}
     */
    private function readCsvRows(string $filePath): array
    {
        $handle = fopen($filePath, 'rb');
        if ($handle === false) {
            return [[], ','];
        }

        $firstLine = fgets($handle);
        if ($firstLine === false) {
            fclose($handle);
            return [[], ','];
        }

        $delimiter = substr_count($firstLine, ';') > substr_count($firstLine, ',') ? ';' : ',';
        rewind($handle);

        $rows = [];
        while (($row = fgetcsv($handle, 0, $delimiter)) !== false) {
            if (!is_array($row)) {
                continue;
            }

            if (isset($row[0])) {
                $row[0] = preg_replace('/^\xEF\xBB\xBF/', '', (string) $row[0]) ?? (string) $row[0];
            }

            $rows[] = array_map(static fn ($value): string => trim((string) $value), $row);
        }

        fclose($handle);

        return [$rows, $delimiter];
    }

    /**
     * @param array<int, string> $firstRow
     * @param array<string, array<int, string>> $aliases
     * @return array<string, int>|null
     */
    private function detectHeaderMap(array $firstRow, array $aliases): ?array
    {
        $normalizedHeaders = array_map(fn ($value): string => $this->normalizeToken((string) $value), $firstRow);

        $map = [];
        foreach ($aliases as $field => $fieldAliases) {
            foreach ($normalizedHeaders as $index => $header) {
                if (in_array($header, $fieldAliases, true)) {
                    $map[$field] = $index;
                    break;
                }
            }
        }

        return count($map) > 0 ? $map : null;
    }

    /**
     * @param array<int, string> $headers
     * @return array<string, int>
     */
    private function defaultHeaderMap(array $headers): array
    {
        $map = [];
        foreach ($headers as $index => $field) {
            $map[$field] = $index;
        }

        return $map;
    }

    /**
     * @param array<int, string> $row
     */
    private function isEmptyRow(array $row): bool
    {
        foreach ($row as $value) {
            if (trim((string) $value) !== '') {
                return false;
            }
        }

        return true;
    }

    /**
     * @param array<int, string> $row
     * @param array<string, int> $headerMap
     */
    private function rowValue(array $row, array $headerMap, string $field): string
    {
        if (!array_key_exists($field, $headerMap)) {
            return '';
        }

        return trim((string) ($row[$headerMap[$field]] ?? ''));
    }

    private function normalizeToken(string $value): string
    {
        $value = Str::ascii(trim($value));
        $value = mb_strtolower($value, 'UTF-8');
        $value = preg_replace('/[^a-z0-9]+/', '_', $value) ?? '';

        return trim($value, '_');
    }

    private function nullableString(string $value): ?string
    {
        $value = trim($value);
        return $value === '' ? null : $value;
    }

    private function sanitizeUpperAlnum(string $value): string
    {
        $value = Str::upper(Str::ascii(trim($value)));
        return preg_replace('/[^A-Z0-9]+/', '', $value) ?? '';
    }

    private function sanitizeDigits(string $value): string
    {
        return preg_replace('/\D+/', '', trim($value)) ?? '';
    }

    private function fitsMaxLength(string $value, int $maxLength): bool
    {
        return mb_strlen(trim($value), 'UTF-8') <= $maxLength;
    }

    private function nullableDate(string $value): ?string
    {
        $value = trim($value);
        if ($value === '') {
            return null;
        }

        $formats = ['Y-m-d', 'd/m/Y', 'd-m-Y', 'Y/m/d', 'm/d/Y'];
        foreach ($formats as $format) {
            try {
                return Carbon::createFromFormat($format, $value)->toDateString();
            } catch (\Throwable $e) {
                // intentar otro formato
            }
        }

        try {
            return Carbon::parse($value)->toDateString();
        } catch (\Throwable $e) {
            return null;
        }
    }

    private function nullableInteger(string $value): ?int
    {
        $value = trim($value);
        if ($value === '' || !is_numeric($value)) {
            return null;
        }

        return (int) $value;
    }

    private function nullableDecimal(string $value): ?float
    {
        $value = str_replace(',', '', trim($value));
        if ($value === '' || !is_numeric($value)) {
            return null;
        }

        return (float) $value;
    }

    private function parseBoolean(string $value, bool $default = true): bool
    {
        $normalized = $this->normalizeToken($value);
        if ($normalized === '') {
            return $default;
        }

        if (in_array($normalized, ['1', 'si', 'sí', 'yes', 'true', 'activo', 'active'], true)) {
            return true;
        }

        if (in_array($normalized, ['0', 'no', 'false', 'inactivo', 'inactive'], true)) {
            return false;
        }

        return $default;
    }

    /**
     * @param array<int, string> $errors
     */
    private function appendError(array &$errors, int $maxErrors, string $message): void
    {
        if (count($errors) < $maxErrors) {
            $errors[] = $message;
        }
    }
}
