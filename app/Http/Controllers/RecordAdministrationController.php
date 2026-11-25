<?php

namespace App\Http\Controllers;

use App\Services\SystemAudit;
use Illuminate\Contracts\Encryption\DecryptException;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RecordAdministrationController extends Controller
{
    public function index(Request $request): View
    {
        abort_unless($request->user()?->canDeleteAnyRecord(), 403);
        $tables = $this->tables();
        $data = $request->validate([
            'table' => ['nullable', 'string', Rule::in($tables)],
            'search' => ['nullable', 'string', 'max:150'],
        ]);
        $table = $data['table'] ?? ($tables[0] ?? null);
        $search = trim($data['search'] ?? '');
        $columns = $table ? $this->visibleColumns($table) : [];
        $keyColumns = $table ? $this->keyColumns($table) : [];
        $records = null;
        if ($table) {
            $query = DB::table($table);
            if ($search !== '' && $columns !== []) {
                $query->where(function ($query) use ($columns, $search): void {
                    foreach ($columns as $column) {
                        $query->orWhere($column, 'like', '%'.$search.'%');
                    }
                });
            }
            foreach ($keyColumns as $column) {
                $query->orderBy($column);
            }
            $records = $query->paginate(25)->withQueryString();
            $records->setCollection($records->getCollection()->map(function ($record) use ($table, $columns, $keyColumns, $request) {
                $values = (array) $record;

                return [
                    'values' => array_intersect_key($values, array_flip($columns)),
                    'token' => $keyColumns === [] ? null : Crypt::encryptString(json_encode([
                        'table' => $table,
                        'actor_id' => $request->user()->id,
                        'key' => array_intersect_key($values, array_flip($keyColumns)),
                        'fingerprint' => $this->fingerprint($values),
                    ], JSON_THROW_ON_ERROR)),
                ];
            }));
        }

        return view('records.index', compact('tables', 'table', 'search', 'columns', 'keyColumns', 'records'));
    }

    public function destroy(Request $request, string $table): RedirectResponse
    {
        abort_unless($request->user()?->canDeleteAnyRecord(), 403);
        abort_unless(in_array($table, $this->tables(), true), 404);
        $data = $request->validate(['record' => ['required', 'string']]);
        try {
            $selection = json_decode(Crypt::decryptString($data['record']), true, 512, JSON_THROW_ON_ERROR);
        } catch (DecryptException|\JsonException $exception) {
            throw ValidationException::withMessages(['record' => 'La selección no es válida. Actualiza la tabla e intenta de nuevo.']);
        }
        $keyColumns = $this->keyColumns($table);
        abort_unless(is_array($selection) && ($selection['table'] ?? null) === $table
            && ($selection['actor_id'] ?? null) === $request->user()->id
            && $keyColumns !== [] && is_array($selection['key'] ?? null)
            && count($selection['key']) === count($keyColumns)
            && array_diff($keyColumns, array_keys($selection['key'])) === [], 422);

        try {
            DB::transaction(function () use ($table, $selection, $request): void {
                $query = DB::table($table)->where($selection['key']);
                $record = $query->lockForUpdate()->first();
                abort_unless($record, 404);
                if (! hash_equals($this->fingerprint((array) $record), (string) ($selection['fingerprint'] ?? ''))) {
                    throw ValidationException::withMessages(['record' => 'El registro cambió. Actualiza la tabla y revisa los datos antes de eliminarlo.']);
                }
                $query->delete();
                app(SystemAudit::class)->record('deleted', 'Eliminación especial del superadmin en '.$table, [
                    'subject_type' => $table,
                    'subject_id' => $table === 'sessions' ? '[OCULTO]' : implode(':', array_values($selection['key'])),
                    'before_values' => array_intersect_key((array) $record, array_flip($this->visibleColumns($table))),
                    'after_values' => [],
                    'metadata' => ['special_permission' => 'delete_any_record'],
                ], $request->user());
            });
        } catch (QueryException $exception) {
            if (in_array((string) $exception->getCode(), ['23000', '23503', '19'], true)) {
                throw ValidationException::withMessages(['record' => 'Este registro tiene datos relacionados que impiden eliminarlo. Revisa y elimina primero esos registros desde su tabla.']);
            }
            throw $exception;
        }

        return redirect()->route('records.index', ['table' => $table])->with('status', 'Registro eliminado de '.$table.'.');
    }

    private function tables(): array
    {
        return collect(Schema::getTables())->pluck('name')->sort()->values()->all();
    }

    private function keyColumns(string $table): array
    {
        $indexes = collect(Schema::getIndexes($table));
        $nullableColumns = collect(Schema::getColumns($table))->where('nullable', true)->pluck('name')->all();
        $index = $indexes->firstWhere('primary', true) ?? $indexes->first(fn ($index) => $index['unique'] && array_intersect($index['columns'], $nullableColumns) === []);

        return $index['columns'] ?? [];
    }

    private function visibleColumns(string $table): array
    {
        return array_values(array_filter(Schema::getColumnListing($table), fn ($column) => ! preg_match('/password|passwd|secret|token|session|cookie|credential|payload|exception|^value$|^options$/i', $column)
            && ! ($table === 'sessions' && $column === 'id')));
    }

    private function fingerprint(array $record): string
    {
        return hash('sha256', serialize($record));
    }
}
