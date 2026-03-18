<?php

namespace App\Http\Controllers;

use App\Models\Personnel;
use App\Models\PersonnelCardexEntry;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Illuminate\View\View;

class CardexImportController extends Controller
{
    private const CODES = [
        'A' => 'Asistencia',
        'V' => 'Vacaciones',
        'F' => 'Falta',
        'I' => 'Incapacidad',
        'PSG' => 'Permiso sin goce',
        'PCG' => 'Permiso con goce',
        'G' => 'Guardia',
        'D' => 'Descanso',
        'S' => 'Sin asignar',
    ];

    public function index(): View
    {
        return view('hr.cardex-import', [
            'codes' => self::CODES,
        ]);
    }

    public function template(): Response
    {
        $today = now()->toDateString();
        $rows = [
            ['nombre', 'clave', 'fecha'],
            ['Luis Hernandez Garcia', 'A', $today],
            ['Carla Lopez Ramirez', 'F', $today],
            ['Paola Mendez Ruiz', 'PCG', $today],
            ['Jorge Santos Vega', 'S', $today],
        ];

        $csv = '';
        foreach ($rows as $row) {
            $escaped = array_map(function (string $value): string {
                $value = str_replace('"', '""', $value);
                return '"' . $value . '"';
            }, $row);
            $csv .= implode(',', $escaped) . "\r\n";
        }

        return response($csv, 200, [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="plantilla_kardex.csv"',
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'csv_file' => ['required', 'file', 'mimes:csv,txt', 'max:5120'],
        ]);

        [$rows] = $this->readCsvRows($request->file('csv_file')->getRealPath());
        if (count($rows) === 0) {
            return back()->withErrors([
                'csv_file' => 'El archivo CSV esta vacio o no se pudo leer.',
            ]);
        }

        $headerMap = $this->detectHeaderMap($rows[0] ?? []);
        $startIndex = $headerMap ? 1 : 0;
        if (!$headerMap) {
            $headerMap = ['name' => 0, 'code' => 1, 'date' => 2];
        }

        /** @var Collection<string, Collection<int, Personnel>> $personnelByName */
        $personnelByName = Personnel::orderBy('id')
            ->get()
            ->groupBy(function (Personnel $personnel): string {
                return $this->normalizeName($personnel->full_name);
            });

        $createdCount = 0;
        $updatedCount = 0;
        $processedCount = 0;
        $errorMessages = [];
        $maxErrorsToShow = 80;

        foreach ($rows as $index => $row) {
            if ($index < $startIndex) {
                continue;
            }

            if ($this->isEmptyRow($row)) {
                continue;
            }

            $lineNumber = $index + 1;
            $name = trim((string) ($row[$headerMap['name']] ?? ''));
            $code = strtoupper(trim((string) ($row[$headerMap['code']] ?? '')));
            $rawDate = trim((string) ($row[$headerMap['date']] ?? ''));

            if ($name === '' || $code === '' || $rawDate === '') {
                $this->appendError(
                    $errorMessages,
                    $maxErrorsToShow,
                    "Linea {$lineNumber}: faltan datos (nombre, clave o fecha)."
                );
                continue;
            }

            if (!array_key_exists($code, self::CODES)) {
                $this->appendError(
                    $errorMessages,
                    $maxErrorsToShow,
                    "Linea {$lineNumber}: la clave '{$code}' no es valida."
                );
                continue;
            }

            $entryDate = $this->parseDate($rawDate);
            if (!$entryDate) {
                $this->appendError(
                    $errorMessages,
                    $maxErrorsToShow,
                    "Linea {$lineNumber}: la fecha '{$rawDate}' no es valida."
                );
                continue;
            }

            $normalizedName = $this->normalizeName($name);
            $matches = $personnelByName->get($normalizedName, collect());
            if ($matches->count() === 0) {
                $this->appendError(
                    $errorMessages,
                    $maxErrorsToShow,
                    "Linea {$lineNumber}: no se encontro personal con nombre '{$name}'."
                );
                continue;
            }
            if ($matches->count() > 1) {
                $this->appendError(
                    $errorMessages,
                    $maxErrorsToShow,
                    "Linea {$lineNumber}: nombre '{$name}' duplicado en personal; usa un nombre mas especifico."
                );
                continue;
            }

            $personnel = $matches->first();
            $entry = PersonnelCardexEntry::firstOrNew([
                'personnel_id' => $personnel->id,
                'entry_date' => $entryDate,
            ]);
            $isNew = !$entry->exists;

            $entry->code = $code;
            $entry->notes = 'Carga masiva CSV';
            $entry->updated_by = optional($request->user())->id;
            $entry->save();

            if ($isNew) {
                $createdCount++;
            } else {
                $updatedCount++;
            }
            $processedCount++;
        }

        if ($processedCount === 0) {
            return back()
                ->withErrors([
                    'csv_file' => 'No se importo ningun registro valido.',
                ])
                ->with('import_errors', $errorMessages);
        }

        $status = "Importacion completada. Nuevos: {$createdCount}. Actualizados: {$updatedCount}.";
        if (count($errorMessages) > 0) {
            $status .= ' Se omitieron algunas lineas con error.';
        }

        return redirect()->route('cardex.import.index')
            ->with('status', $status)
            ->with('import_errors', $errorMessages);
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
     * @return array{name:int, code:int, date:int}|null
     */
    private function detectHeaderMap(array $firstRow): ?array
    {
        $normalizedHeaders = array_map(function ($value): string {
            return $this->normalizeToken((string) $value);
        }, $firstRow);

        $aliases = [
            'name' => ['nombre', 'nombre_persona', 'persona', 'empleado', 'trabajador', 'nombre_completo'],
            'code' => ['clave', 'codigo', 'tipo_clave'],
            'date' => ['fecha', 'fecha_registro', 'dia'],
        ];

        $map = [];
        foreach ($aliases as $field => $fieldAliases) {
            foreach ($normalizedHeaders as $index => $header) {
                if (in_array($header, $fieldAliases, true)) {
                    $map[$field] = $index;
                    break;
                }
            }
        }

        if (count($map) === 3) {
            return $map;
        }

        return null;
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

    private function normalizeToken(string $value): string
    {
        $value = Str::ascii(trim($value));
        $value = mb_strtolower($value, 'UTF-8');
        $value = preg_replace('/[^a-z0-9]+/', '_', $value) ?? '';

        return trim($value, '_');
    }

    private function normalizeName(string $value): string
    {
        $value = Str::ascii(trim($value));
        $value = mb_strtolower($value, 'UTF-8');
        $value = preg_replace('/[^a-z0-9]+/', ' ', $value) ?? '';

        return trim($value);
    }

    private function parseDate(string $value): ?string
    {
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
