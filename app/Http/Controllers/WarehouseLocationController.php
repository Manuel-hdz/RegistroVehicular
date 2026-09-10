<?php

namespace App\Http\Controllers;

use App\Models\CostCenter;
use App\Models\WarehouseLocation;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class WarehouseLocationController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorizeManager($request);
        [$costCenters, $selectedCostCenter] = $this->warehouseContext($request);

        $locations = $selectedCostCenter->warehouseLocations()
            ->with('updater')
            ->orderByDesc('active')
            ->orderBy('name')
            ->get();

        return view('warehouse.locations', compact('costCenters', 'selectedCostCenter', 'locations'));
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorizeManager($request);
        $data = $request->validate([
            'cost_center_id' => ['required', 'integer'],
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:255'],
        ]);
        $costCenter = $this->authorizedCostCenter($request, (int) $data['cost_center_id']);
        $this->ensureUniqueName($costCenter, $data['name']);

        $costCenter->warehouseLocations()->create([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'active' => true,
            'created_by' => $request->user()->id,
            'updated_by' => $request->user()->id,
        ]);

        return redirect()
            ->route('warehouse.locations.index', ['cost_center_id' => $costCenter->id])
            ->with('status', 'Ubicación creada.');
    }

    public function update(Request $request, WarehouseLocation $warehouseLocation): RedirectResponse
    {
        $this->authorizeManager($request);
        $costCenter = $this->authorizedCostCenter($request, (int) $warehouseLocation->cost_center_id);
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:255'],
            'active' => ['nullable', 'boolean'],
        ]);
        $this->ensureUniqueName($costCenter, $data['name'], $warehouseLocation);

        $active = $request->has('active');
        if (! $active && $warehouseLocation->active && $costCenter->warehouseLocations()->where('active', true)->count() === 1) {
            throw ValidationException::withMessages([
                'active' => 'Debe permanecer al menos una ubicación activa en el centro de costos.',
            ]);
        }

        $warehouseLocation->update([
            'name' => $data['name'],
            'description' => $data['description'] ?? null,
            'active' => $active,
            'updated_by' => $request->user()->id,
        ]);

        return redirect()
            ->route('warehouse.locations.index', ['cost_center_id' => $costCenter->id])
            ->with('status', 'Ubicación actualizada.');
    }

    private function authorizeManager(Request $request): void
    {
        abort_unless(
            $request->user()?->canManageWarehouseLocations(),
            403,
            'Solo un administrador de almacén puede actualizar las ubicaciones.'
        );
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

    private function authorizedCostCenter(Request $request, int $costCenterId): CostCenter
    {
        [$costCenters] = $this->warehouseContext($request);
        $costCenter = $costCenters->firstWhere('id', $costCenterId);
        abort_unless($costCenter, 403, 'No tienes acceso al centro de costos seleccionado.');

        return $costCenter;
    }

    private function ensureUniqueName(
        CostCenter $costCenter,
        string $name,
        ?WarehouseLocation $currentLocation = null
    ): void {
        $query = $costCenter->warehouseLocations()
            ->where('normalized_name', WarehouseLocation::normalizeName($name));

        if ($currentLocation) {
            $query->whereKeyNot($currentLocation->id);
        }

        if ($query->exists()) {
            throw ValidationException::withMessages([
                'name' => 'Ya existe una ubicación con ese nombre en el centro de costos.',
            ]);
        }
    }
}
