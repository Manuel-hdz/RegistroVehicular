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
        if (count($errorMessages) > 0) {
            $status .= ' Se omitieron algunas lineas con error.';
        }

        return redirect()->route($resultRoute)
            ->with('status', $status)
            ->with('imported_type', $type)
            ->with('import_errors', $errorMessages);
    }

    /**
     * @return array<int, array{key:string,label:string,description:string,filename:string,headers:array<int,string>,examples:array<int,array<int,string>>,aliases:array<string,array<int,string>>,notes:array<int,string>}>
     */
    private function importDefinitions(): array
    {
        return [
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
     * @param array<int, array<int, string>> $rows
     */
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
