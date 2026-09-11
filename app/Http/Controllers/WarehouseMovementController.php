<?php

namespace App\Http\Controllers;

use App\Models\CostCenter;
use App\Models\Part;
use App\Models\WarehouseEntry;
use App\Models\WarehouseEntryMaterial;
use App\Models\WarehouseLocation;
use App\Models\WarehouseMaterialExit;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class WarehouseMovementController extends Controller
{
    public function index(Request $request): View
    {
        [$costCenters, $selectedCostCenter] = $this->warehouseContext($request);
        $costCenterId = $selectedCostCenter->id;
        $warehouseLocations = $selectedCostCenter->warehouseLocations()
            ->where('active', true)
            ->orderBy('name')
            ->get();
        $weekStart = now()->startOfWeek();
        $weekEnd = now()->endOfWeek();
        $monthStart = now()->startOfYear();

        $weeklyEntries = WarehouseEntry::where('cost_center_id', $costCenterId)
            ->whereBetween('entry_date', [$weekStart, $weekEnd])
            ->count();
        $weeklyDepartures = WarehouseMaterialExit::where('cost_center_id', $costCenterId)
            ->whereBetween('exit_date', [$weekStart, $weekEnd])
            ->count();
        $latestEntries = WarehouseEntry::query()
            ->where('cost_center_id', $costCenterId)
            ->orderByDesc('entry_date')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();
        $registeredEntries = WarehouseEntry::with(['materials.part', 'materials.location', 'registeredBy'])
            ->where('cost_center_id', $costCenterId)
            ->orderByDesc('entry_date')
            ->orderByDesc('created_at')
            ->get();
        $latestDepartures = WarehouseMaterialExit::query()
            ->where('cost_center_id', $costCenterId)
            ->orderByDesc('exit_date')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();
        $registeredDepartures = WarehouseMaterialExit::with(['part', 'registeredBy'])
            ->where('cost_center_id', $costCenterId)
            ->orderByDesc('exit_date')
            ->orderByDesc('created_at')
            ->get();
        $materialOptions = Part::query()
            ->whereHas('entryMaterials.entry', fn ($query) => $query->where('cost_center_id', $costCenterId))
            ->withSum([
                'entryMaterials as total_entries' => fn ($query) => $query->whereHas(
                    'entry',
                    fn ($entryQuery) => $entryQuery->where('cost_center_id', $costCenterId)
                ),
            ], 'quantity')
            ->withSum([
                'materialExits as total_exits' => fn ($query) => $query->where('cost_center_id', $costCenterId),
            ], 'quantity')
            ->orderBy('name')
            ->get(['id', 'clave', 'name', 'characteristics'])
            ->map(function (Part $part): Part {
                $part->stock_quantity = max(0, (float) ($part->total_entries ?? 0) - (float) ($part->total_exits ?? 0));

                return $part;
            })
            ->values();
        $lastLocationIds = WarehouseEntryMaterial::query()
            ->whereIn('part_id', $materialOptions->pluck('id'))
            ->whereNotNull('warehouse_location_id')
            ->whereHas('entry', fn ($query) => $query->where('cost_center_id', $costCenterId))
            ->orderByDesc('id')
            ->get(['part_id', 'warehouse_location_id'])
            ->unique('part_id')
            ->pluck('warehouse_location_id', 'part_id');
        $entryMaterialSuggestions = $materialOptions
            ->map(fn (Part $part) => [
                'id' => $part->id,
                'name' => $part->name,
                'clave' => $part->clave,
                'characteristics' => is_array($part->characteristics) ? array_values($part->characteristics) : [],
                'warehouse_location_id' => $lastLocationIds->get($part->id),
            ])
            ->values();

        $departuresByMonth = WarehouseMaterialExit::where('cost_center_id', $costCenterId)
            ->where('exit_date', '>=', $monthStart)
            ->get(['exit_date'])
            ->countBy(fn (WarehouseMaterialExit $exit) => $exit->exit_date->month);

        $entriesByMonth = WarehouseEntry::where('cost_center_id', $costCenterId)
            ->where('entry_date', '>=', $monthStart)
            ->get(['entry_date'])
            ->countBy(fn (WarehouseEntry $entry) => $entry->entry_date->month);

        $monthlyLabels = [];
        $monthlyEntries = [];
        $monthlyDepartures = [];

        foreach (CarbonPeriod::create($monthStart, '1 month', now()->startOfMonth()) as $month) {
            $monthNumber = $month->month;
            $monthlyLabels[] = Carbon::create(null, $monthNumber, 1)->locale('es')->translatedFormat('M');
            $monthlyEntries[] = (int) ($entriesByMonth[$monthNumber] ?? 0);
            $monthlyDepartures[] = (int) ($departuresByMonth[$monthNumber] ?? 0);
        }

        return view('warehouse.movements', [
            'weeklyEntries' => $weeklyEntries,
            'weeklyDepartures' => $weeklyDepartures,
            'latestEntries' => $latestEntries,
            'registeredEntries' => $registeredEntries,
            'latestDepartures' => $latestDepartures,
            'registeredDepartures' => $registeredDepartures,
            'materialOptions' => $materialOptions,
            'entryMaterialSuggestions' => $entryMaterialSuggestions,
            'warehouseLocations' => $warehouseLocations,
            'monthlyLabels' => $monthlyLabels,
            'monthlyEntries' => $monthlyEntries,
            'monthlyDepartures' => $monthlyDepartures,
            'costCenters' => $costCenters,
            'selectedCostCenter' => $selectedCostCenter,
        ]);
    }

    public function inventory(Request $request): View
    {
        [$costCenters, $selectedCostCenter] = $this->warehouseContext($request);
        $costCenterId = $selectedCostCenter->id;
        $search = trim((string) $request->query('search', ''));

        $materials = Part::query()
            ->whereHas('entryMaterials.entry', fn ($query) => $query->where('cost_center_id', $costCenterId))
            ->with([
                'latestMaterialExit' => fn ($query) => $query->where('cost_center_id', $costCenterId),
                'entryMaterials' => fn ($query) => $query
                    ->whereHas('entry', fn ($entryQuery) => $entryQuery->where('cost_center_id', $costCenterId))
                    ->with('location'),
            ])
            ->withSum([
                'entryMaterials as total_entries' => fn ($query) => $query->whereHas(
                    'entry',
                    fn ($entryQuery) => $entryQuery->where('cost_center_id', $costCenterId)
                ),
            ], 'quantity')
            ->withSum([
                'materialExits as total_exits' => fn ($query) => $query->where('cost_center_id', $costCenterId),
            ], 'quantity')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('clave', 'like', '%'.$search.'%')
                        ->orWhere('name', 'like', '%'.$search.'%')
                        ->orWhere('characteristics', 'like', '%'.$search.'%');
                });
            })
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $materials->getCollection()->transform(function (Part $part): Part {
            $part->stock_quantity = max(0, (float) ($part->total_entries ?? 0) - (float) ($part->total_exits ?? 0));

            return $part;
        });

        return view('warehouse.inventory', [
            'materials' => $materials,
            'search' => $search,
            'costCenters' => $costCenters,
            'selectedCostCenter' => $selectedCostCenter,
        ]);
    }

    public function storeMaterial(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'entry_type' => ['nullable', 'string', 'in:Pase de salida,Compra'],
            'cost_center_id' => ['required', 'integer'],
            'entry_date' => ['required', 'date'],
            'invoice_file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:10240'],
            'materials' => ['required', 'array', 'min:1'],
            'materials.*.part_id' => ['nullable', 'integer', 'exists:parts,id'],
            'materials.*.warehouse_location_id' => ['required', 'integer', 'exists:warehouse_locations,id'],
            'materials.*.name' => ['required', 'string', 'max:150'],
            'materials.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'materials.*.characteristics' => ['nullable', 'string', 'max:1000'],
        ]);
        $costCenter = $this->authorizedCostCenter($request, (int) $data['cost_center_id']);
        $locationIds = collect($data['materials'])->pluck('warehouse_location_id')->map(fn ($id) => (int) $id)->unique();
        $authorizedLocationCount = WarehouseLocation::query()
            ->where('cost_center_id', $costCenter->id)
            ->where('active', true)
            ->whereIn('id', $locationIds)
            ->count();

        if ($authorizedLocationCount !== $locationIds->count()) {
            throw ValidationException::withMessages([
                'materials' => 'Selecciona ubicaciones activas que pertenezcan al centro de costos actual.',
            ]);
        }

        $invoicePath = $request->hasFile('invoice_file')
            ? $request->file('invoice_file')->store('facturas-compras', 'public')
            : null;

        $registeredBy = $request->user();
        $entry = DB::transaction(function () use ($data, $invoicePath, $costCenter, $registeredBy): WarehouseEntry {
            $entry = WarehouseEntry::create([
                'cost_center_id' => $costCenter->id,
                'registered_by_user_id' => $registeredBy?->id,
                'registered_by_username' => $registeredBy?->username,
                'entry_type' => $data['entry_type'] ?? null,
                'entry_date' => $data['entry_date'],
                'invoice_path' => $invoicePath,
            ]);

            $entry->update([
                'entry_key' => 'ENT-'.$entry->entry_date->format('Ymd').'-'.str_pad((string) $entry->id, 5, '0', STR_PAD_LEFT),
            ]);

            $groupedMaterials = [];

            foreach ($data['materials'] as $materialData) {
                $characteristics = $this->normalizeCharacteristics((string) ($materialData['characteristics'] ?? ''));
                $part = $this->resolveMaterialPart($materialData, $characteristics);
                $locationId = (int) $materialData['warehouse_location_id'];
                $groupKey = $part->id.':'.$locationId;
                $groupedMaterials[$groupKey] ??= [
                    'part_id' => $part->id,
                    'warehouse_location_id' => $locationId,
                    'quantity' => 0,
                ];
                $groupedMaterials[$groupKey]['quantity'] += (float) $materialData['quantity'];
            }

            foreach ($groupedMaterials as $groupedMaterial) {
                $entry->materials()->create($groupedMaterial);
            }

            return $entry;
        });

        return redirect()
            ->route('warehouse.movements', ['cost_center_id' => $costCenter->id])
            ->with('status', 'Entrada registrada con folio '.$entry->entry_key.'.');
    }

    public function storeMaterialExit(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'part_id' => ['required', 'exists:parts,id'],
            'cost_center_id' => ['required', 'integer'],
            'quantity' => ['nullable', 'numeric', 'min:0.01'],
            'exit_date' => ['nullable', 'date'],
            'dispatched_by' => ['nullable', 'string', 'max:180'],
            'carried_by' => ['nullable', 'string', 'max:180'],
            'destination' => ['nullable', 'string', 'max:180'],
            'responsible' => ['nullable', 'string', 'max:180'],
            'invoice_file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:10240'],
        ], [
            'part_id.required' => 'Selecciona un material existente en el inventario.',
            'part_id.exists' => 'El material seleccionado no existe en el inventario.',
        ]);
        $costCenter = $this->authorizedCostCenter($request, (int) $data['cost_center_id']);

        $data['quantity'] = $data['quantity'] ?? 1;
        $data['exit_date'] = $data['exit_date'] ?? now();

        $part = Part::findOrFail($data['part_id']);
        $availableEntries = $part->entryMaterials()
            ->whereHas('entry', fn ($query) => $query->where('cost_center_id', $costCenter->id))
            ->sum('quantity');
        $availableExits = $part->materialExits()
            ->where('cost_center_id', $costCenter->id)
            ->sum('quantity');
        $available = max(0, (float) $availableEntries - (float) $availableExits);

        if ($available <= 0 || (float) $data['quantity'] > $available) {
            return back()
                ->withErrors(['quantity' => 'El material solicitado no tiene existencia suficiente en el inventario.'])
                ->withInput();
        }

        $invoicePath = $request->hasFile('invoice_file')
            ? $request->file('invoice_file')->store('facturas-salidas', 'public')
            : null;
        unset($data['invoice_file']);

        $exit = WarehouseMaterialExit::create([
            ...$data,
            'cost_center_id' => $costCenter->id,
            'registered_by_user_id' => $request->user()?->id,
            'registered_by_username' => $request->user()?->username,
            'source' => $data['dispatched_by'] ?? '',
            'destination' => $data['destination'] ?? '',
            'responsible' => $data['responsible'] ?? '',
            'invoice_path' => $invoicePath,
            'status' => 'en_curso',
        ]);
        $exit->update([
            'voucher_number' => 'VALE-'.$exit->exit_date->format('Ymd').'-'.str_pad((string) $exit->id, 5, '0', STR_PAD_LEFT),
        ]);

        return redirect()
            ->route('warehouse.material-exits.voucher', [
                'warehouseMaterialExit' => $exit,
                'cost_center_id' => $costCenter->id,
            ])
            ->with('status', 'Salida registrada. Vale generado.');
    }

    public function markExitDelivered(Request $request, WarehouseMaterialExit $warehouseMaterialExit): RedirectResponse
    {
        $costCenter = $this->authorizedCostCenter($request, (int) $warehouseMaterialExit->cost_center_id);

        if ($warehouseMaterialExit->status !== 'en_curso') {
            return redirect()
                ->route('warehouse.inventory', ['cost_center_id' => $costCenter->id])
                ->with('status', 'La salida ya fue marcada como entregada.');
        }

        $warehouseMaterialExit->update([
            'status' => 'entregado',
            'delivered_at' => now(),
        ]);

        return redirect()
            ->route('warehouse.inventory', ['cost_center_id' => $costCenter->id])
            ->with('status', 'Material marcado como entregado.');
    }

    public function voucher(Request $request, WarehouseMaterialExit $warehouseMaterialExit): View
    {
        $this->authorizedCostCenter($request, (int) $warehouseMaterialExit->cost_center_id);
        $warehouseMaterialExit->load(['part', 'costCenter', 'registeredBy']);

        return view('warehouse.exit-voucher', ['exit' => $warehouseMaterialExit]);
    }

    private function normalizeCharacteristics(string $rawCharacteristics): array
    {
        return Part::normalizeCharacteristics($rawCharacteristics);
    }

    /**
     * @param  array{name:string, quantity:mixed, characteristics?:string|null, part_id?:mixed, warehouse_location_id:mixed}  $materialData
     * @param  array<int, string>  $characteristics
     */
    private function resolveMaterialPart(array $materialData, array $characteristics): Part
    {
        $name = (string) Str::of($materialData['name'])->squish();
        $identityKey = Part::identityKey($name, $characteristics);

        if (! empty($materialData['part_id'])) {
            $selectedPart = Part::find((int) $materialData['part_id']);
            if ($selectedPart && $this->partIdentity($selectedPart) === $identityKey) {
                return $selectedPart;
            }
        }

        $clave = $this->generateMaterialClave($name, $characteristics);
        $matchingPart = Part::where('identity_key', $identityKey)->first();

        return $matchingPart ?? Part::create([
            'clave' => $clave,
            'name' => $name,
            'characteristics' => $characteristics,
            'unit_cost' => 0,
            'active' => true,
        ]);
    }

    private function partIdentity(Part $part): string
    {
        return Part::identityKey($part->name, $part->characteristics);
    }

    private function generateMaterialClave(string $name, array $characteristics = []): string
    {
        $cleanName = Str::of($name)
            ->ascii()
            ->upper()
            ->replaceMatches('/[^A-Z0-9\s]/', '')
            ->trim();

        $prefix = $cleanName
            ->replaceMatches('/\s+/', '')
            ->substr(0, 6)
            ->padRight(6, 'X');

        $identity = (string) $cleanName.'|'.implode('|', $characteristics);
        $hash = 0;
        for ($index = 0; $index < strlen($identity); $index++) {
            $hash = (($hash << 5) - $hash + ord($identity[$index])) & 0xFFFFFFFF;
        }
        $suffix = strtoupper(substr(str_pad(dechex($hash), 6, '0', STR_PAD_LEFT), 0, 6));

        return 'MAT-'.$prefix.'-'.$suffix;
    }

    public function updateMaterial(Request $request, Part $part): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
        ]);

        $part->update($data);

        return redirect()
            ->route('warehouse.movements')
            ->with('status', 'Material actualizado.');
    }

    public function destroyMaterial(Part $part): RedirectResponse
    {
        $part->delete();

        return redirect()
            ->route('warehouse.movements')
            ->with('status', 'Material eliminado.');
    }

    /**
     * @return array{0: \Illuminate\Support\Collection<int, CostCenter>, 1: CostCenter}
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
        $selectedCostCenter = $requestedId > 0
            ? $costCenters->firstWhere('id', $requestedId)
            : null;

        if ($hasExplicitSelection) {
            abort_unless($selectedCostCenter, 403, 'No tienes acceso al centro de costos seleccionado.');
        }

        $selectedCostCenter ??= $costCenters->first();

        $request->session()->put('warehouse.cost_center_id', $selectedCostCenter->id);

        return [$costCenters, $selectedCostCenter];
    }

    private function authorizedCostCenter(Request $request, int $costCenterId): CostCenter
    {
        [$costCenters] = $this->warehouseContext($request);
        $costCenter = $costCenters->firstWhere('id', $costCenterId);

        abort_unless($costCenter, 403, 'No tienes acceso al centro de costos seleccionado.');

        return $costCenter;
    }
}
