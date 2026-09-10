<?php

namespace App\Http\Controllers;

use App\Models\CostCenter;
use App\Models\WarehouseEntryMaterial;
use App\Models\WarehouseMaterialExit;
use App\Services\WarehouseReportExcelExporter;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class WarehouseReportController extends Controller
{
    public function index(Request $request): View
    {
        [$costCenters, $selectedCostCenter] = $this->warehouseContext($request);
        $filters = $this->filters($request);

        $entryQuery = $this->entryQuery($selectedCostCenter, $filters['date_from'], $filters['date_to']);
        $exitQuery = $this->exitQuery($selectedCostCenter, $filters['date_from'], $filters['date_to']);

        $entryCount = (clone $entryQuery)->distinct('warehouse_entries.id')->count('warehouse_entries.id');
        $entryUnits = (float) (clone $entryQuery)->sum('warehouse_entry_materials.quantity');
        $exitCount = (clone $exitQuery)->count();
        $exitUnits = (float) (clone $exitQuery)->sum('quantity');

        $entries = in_array($filters['movement_type'], ['all', 'entries'], true)
            ? (clone $entryQuery)
                ->orderByDesc('warehouse_entries.entry_date')
                ->orderByDesc('warehouse_entry_materials.id')
                ->paginate(30, ['warehouse_entry_materials.*'], 'entries_page')
                ->withQueryString()
            : null;
        $exits = in_array($filters['movement_type'], ['all', 'exits'], true)
            ? (clone $exitQuery)
                ->orderByDesc('exit_date')
                ->orderByDesc('id')
                ->paginate(30, ['*'], 'exits_page')
                ->withQueryString()
            : null;

        return view('warehouse.reports', compact(
            'costCenters',
            'selectedCostCenter',
            'filters',
            'entries',
            'exits',
            'entryCount',
            'entryUnits',
            'exitCount',
            'exitUnits'
        ));
    }

    public function export(Request $request): StreamedResponse
    {
        [, $selectedCostCenter] = $this->warehouseContext($request);
        $filters = $this->filters($request);
        $entries = in_array($filters['movement_type'], ['all', 'entries'], true)
            ? $this->entryQuery($selectedCostCenter, $filters['date_from'], $filters['date_to'])
                ->orderBy('warehouse_entries.entry_date')
                ->get(['warehouse_entry_materials.*'])
            : collect();
        $exits = in_array($filters['movement_type'], ['all', 'exits'], true)
            ? $this->exitQuery($selectedCostCenter, $filters['date_from'], $filters['date_to'])
                ->orderBy('exit_date')
                ->get()
            : collect();
        $filename = sprintf(
            'reporte-almacen-%s-%s-a-%s.csv',
            strtolower($selectedCostCenter->code),
            $filters['date_from'],
            $filters['date_to']
        );

        return response()->streamDownload(function () use ($entries, $exits, $selectedCostCenter): void {
            $output = fopen('php://output', 'w');
            fwrite($output, "\xEF\xBB\xBF");
            fputcsv($output, [
                'Movimiento',
                'Centro de costos',
                'Fecha',
                'Folio',
                'Tipo',
                'Clave',
                'Material',
                'Características',
                'Ubicación',
                'Cantidad',
                'Despachó',
                'Llevó',
                'Destino',
                'Responsable',
                'Estatus',
            ], ',', '"', '');

            foreach ($entries as $entryMaterial) {
                fputcsv($output, $this->sanitizeCsvRow([
                    'Entrada',
                    $selectedCostCenter->name,
                    $entryMaterial->entry?->entry_date?->format('Y-m-d H:i'),
                    $entryMaterial->entry?->entry_key,
                    $entryMaterial->entry?->entry_type,
                    $entryMaterial->part?->clave,
                    $entryMaterial->part?->name,
                    implode(', ', $entryMaterial->part?->characteristics ?? []),
                    $entryMaterial->location?->name,
                    number_format((float) $entryMaterial->quantity, 2, '.', ''),
                    '',
                    '',
                    '',
                    '',
                    '',
                ]), ',', '"', '');
            }

            foreach ($exits as $exit) {
                fputcsv($output, $this->sanitizeCsvRow([
                    'Salida',
                    $selectedCostCenter->name,
                    $exit->exit_date?->format('Y-m-d H:i'),
                    $exit->voucher_number,
                    '',
                    $exit->part?->clave,
                    $exit->part?->name,
                    implode(', ', $exit->part?->characteristics ?? []),
                    '',
                    number_format((float) $exit->quantity, 2, '.', ''),
                    $exit->dispatched_by ?: $exit->source,
                    $exit->carried_by,
                    $exit->destination,
                    $exit->responsible,
                    $exit->status === 'entregado' ? 'Entregado' : 'En curso',
                ]), ',', '"', '');
            }

            fclose($output);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function exportExcel(Request $request, WarehouseReportExcelExporter $exporter): StreamedResponse
    {
        [, $selectedCostCenter] = $this->warehouseContext($request);
        $filters = $this->filters($request);
        $rows = $this->excelRows($selectedCostCenter, $filters);
        $filename = sprintf(
            'reporte-almacen-%s-%s-a-%s.xlsx',
            strtolower($selectedCostCenter->code),
            $filters['date_from'],
            $filters['date_to']
        );

        return response()->streamDownload(
            fn () => $exporter->write($rows, $selectedCostCenter->name, $filters),
            $filename,
            ['Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet']
        );
    }

    /**
     * @return array{date_from:string, date_to:string, movement_type:string}
     */
    private function filters(Request $request): array
    {
        return Validator::make(array_merge([
            'date_from' => now()->startOfMonth()->toDateString(),
            'date_to' => now()->toDateString(),
            'movement_type' => 'all',
        ], $request->only(['date_from', 'date_to', 'movement_type'])), [
            'date_from' => ['required', 'date'],
            'date_to' => ['required', 'date', 'after_or_equal:date_from'],
            'movement_type' => ['required', Rule::in(['all', 'entries', 'exits'])],
        ], [
            'date_to.after_or_equal' => 'La fecha final debe ser igual o posterior a la fecha inicial.',
        ])->validate();
    }

    private function entryQuery(CostCenter $costCenter, string $dateFrom, string $dateTo): Builder
    {
        return WarehouseEntryMaterial::query()
            ->select('warehouse_entry_materials.*')
            ->join('warehouse_entries', 'warehouse_entries.id', '=', 'warehouse_entry_materials.warehouse_entry_id')
            ->where('warehouse_entries.cost_center_id', $costCenter->id)
            ->whereBetween('warehouse_entries.entry_date', [
                Carbon::parse($dateFrom)->startOfDay(),
                Carbon::parse($dateTo)->endOfDay(),
            ])
            ->with(['entry', 'part', 'location']);
    }

    private function exitQuery(CostCenter $costCenter, string $dateFrom, string $dateTo): Builder
    {
        return WarehouseMaterialExit::query()
            ->where('cost_center_id', $costCenter->id)
            ->whereBetween('exit_date', [
                Carbon::parse($dateFrom)->startOfDay(),
                Carbon::parse($dateTo)->endOfDay(),
            ])
            ->with('part');
    }

    /**
     * @param  array{date_from:string, date_to:string, movement_type:string}  $filters
     * @return Collection<int, array<string, mixed>>
     */
    private function excelRows(CostCenter $costCenter, array $filters): Collection
    {
        $rows = collect();

        if (in_array($filters['movement_type'], ['all', 'entries'], true)) {
            $entries = $this->entryQuery($costCenter, $filters['date_from'], $filters['date_to'])
                ->orderBy('warehouse_entries.entry_date')
                ->get(['warehouse_entry_materials.*']);

            foreach ($entries as $entryMaterial) {
                $rows->push([
                    'movement' => 'Entrada',
                    'cost_center' => $costCenter->name,
                    'date' => $entryMaterial->entry?->entry_date,
                    'folio' => $entryMaterial->entry?->entry_key ?? '',
                    'type' => $entryMaterial->entry?->entry_type ?? '',
                    'key' => $entryMaterial->part?->clave ?? '',
                    'material' => $entryMaterial->part?->name ?? '',
                    'characteristics' => implode(', ', $entryMaterial->part?->characteristics ?? []),
                    'location' => $entryMaterial->location?->name ?? '',
                    'quantity' => (float) $entryMaterial->quantity,
                    'dispatched_by' => '',
                    'carried_by' => '',
                    'destination' => '',
                    'responsible' => '',
                    'status' => '',
                ]);
            }
        }

        if (in_array($filters['movement_type'], ['all', 'exits'], true)) {
            $exits = $this->exitQuery($costCenter, $filters['date_from'], $filters['date_to'])
                ->orderBy('exit_date')
                ->get();

            foreach ($exits as $exit) {
                $rows->push([
                    'movement' => 'Salida',
                    'cost_center' => $costCenter->name,
                    'date' => $exit->exit_date,
                    'folio' => $exit->voucher_number ?? '',
                    'type' => '',
                    'key' => $exit->part?->clave ?? '',
                    'material' => $exit->part?->name ?? '',
                    'characteristics' => implode(', ', $exit->part?->characteristics ?? []),
                    'location' => '',
                    'quantity' => (float) $exit->quantity,
                    'dispatched_by' => $exit->dispatched_by ?: $exit->source,
                    'carried_by' => $exit->carried_by ?? '',
                    'destination' => $exit->destination ?? '',
                    'responsible' => $exit->responsible ?? '',
                    'status' => $exit->status === 'entregado' ? 'Entregado' : 'En curso',
                ]);
            }
        }

        return $rows->sortBy(fn (array $row) => $row['date']?->getTimestamp() ?? 0)->values();
    }

    /**
     * @return array{0: Collection<int, CostCenter>, 1: CostCenter}
     */
    private function warehouseContext(Request $request): array
    {
        $costCenters = $request->user()->costCenters()
            ->where('cost_centers.active', true)
            ->orderBy('cost_centers.name')
            ->get();

        if ($costCenters->isEmpty()) {
            $defaultCostCenter = CostCenter::query()
                ->where('code', 'INDIRECTOS-MATRIZ')
                ->where('active', true)
                ->first();
            if ($defaultCostCenter) {
                $costCenters = collect([$defaultCostCenter]);
            }
        }

        abort_if($costCenters->isEmpty(), 403, 'El usuario no tiene centros de costos asignados.');

        $hasExplicitSelection = $request->has('cost_center_id');
        $requestedId = (int) ($hasExplicitSelection
            ? $request->input('cost_center_id')
            : $request->session()->get('warehouse.cost_center_id', 0));
        $selectedCostCenter = $requestedId > 0 ? $costCenters->firstWhere('id', $requestedId) : null;
        abort_if($hasExplicitSelection && ! $selectedCostCenter, 403, 'No tienes acceso al centro de costos seleccionado.');
        $selectedCostCenter ??= $costCenters->first();
        $request->session()->put('warehouse.cost_center_id', $selectedCostCenter->id);

        return [$costCenters, $selectedCostCenter];
    }

    /**
     * @param  array<int, mixed>  $row
     * @return array<int, string>
     */
    private function sanitizeCsvRow(array $row): array
    {
        return array_map(function ($value): string {
            $value = (string) ($value ?? '');

            return preg_match('/^[=+\-@]/', $value) ? "'".$value : $value;
        }, $row);
    }
}
