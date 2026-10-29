<?php

namespace App\Http\Controllers;

use App\Models\MaintenanceBacklogItem;
use App\Models\RequisitionItem;
use App\Models\Vehicle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MaintenanceBacklogController extends Controller
{
    public function index(Request $request): View
    {
        $costCenters = $this->costCenters($request);
        $selectedCostCenter = $request->has('cost_center_id')
            ? $costCenters->firstWhere('id', $request->integer('cost_center_id'))
            : $costCenters->first();
        abort_if($request->has('cost_center_id') && ! $selectedCostCenter, 403);

        $filters = $request->validate([
            'vehicle_id' => ['nullable', 'integer'],
        ]);
        $vehicleQuery = Vehicle::where('active', true)
            ->where('cost_center_id', $selectedCostCenter?->id ?? 0)->orderBy('identifier');
        $vehicles = $vehicleQuery->get();
        $selectedVehicle = ! empty($filters['vehicle_id'])
            ? (clone $vehicleQuery)->findOrFail($filters['vehicle_id']) : null;
        $items = $selectedVehicle && $selectedCostCenter
            ? MaintenanceBacklogItem::where('vehicle_id', $selectedVehicle->id)
                ->where('cost_center_id', $selectedCostCenter->id)
                ->orderByRaw("CASE criticality WHEN 'alto' THEN 0 WHEN 'medio' THEN 1 ELSE 2 END")
                ->orderByDesc('id')->paginate(30)->withQueryString()
            : null;

        $requisitionItems = $selectedVehicle && $selectedCostCenter
            ? RequisitionItem::with('requisition')->whereHas('requisition', fn ($q) => $q->where('cost_center_id', $selectedCostCenter->id))
                ->where(function ($q) use ($selectedVehicle) {
                    $q->where('equipment_vehicle_id', $selectedVehicle->id)
                        ->orWhere(fn ($q) => $q->whereNull('equipment_vehicle_id')
                            ->whereHas('requisition', fn ($q) => $q->where('vehicle_id', $selectedVehicle->id)));
                })->latest()->paginate(20, ['*'], 'requisitions_page')->withQueryString()
            : null;

        return view('maintenance.backlog', compact('costCenters', 'selectedCostCenter', 'vehicles', 'selectedVehicle', 'items', 'requisitionItems'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatedData($request);
        MaintenanceBacklogItem::create($data + ['created_by' => $request->user()->id]);

        return $this->redirectToEquipment($data)->with('status', 'Pieza asignada al equipo.');
    }

    public function update(Request $request, MaintenanceBacklogItem $backlogItem): RedirectResponse
    {
        abort_unless($this->costCenters($request)->contains('id', $backlogItem->cost_center_id), 403);
        abort_unless($request->integer('vehicle_id') === (int) $backlogItem->vehicle_id
            && $request->integer('cost_center_id') === (int) $backlogItem->cost_center_id, 403);
        $data = $this->validatedData($request);
        // Una edición conserva el equipo y el centro de costos del registro.
        abort_unless((int) $data['vehicle_id'] === (int) $backlogItem->vehicle_id
            && (int) $data['cost_center_id'] === (int) $backlogItem->cost_center_id, 403);
        $backlogItem->update($data);

        return $this->redirectToEquipment($data)->with('status', 'Pieza actualizada.');
    }

    private function costCenters(Request $request): Collection
    {
        return $request->user()->costCenters()->where('cost_centers.active', true)
            ->orderBy('cost_centers.name')->get();
    }

    private function validatedData(Request $request): array
    {
        abort_unless($this->costCenters($request)->contains('id', $request->integer('cost_center_id')), 403);
        $data = $request->validate([
            'cost_center_id' => ['required', 'integer'],
            'vehicle_id' => ['required', Rule::exists('vehicles', 'id')->where('active', true)->where('cost_center_id', $request->input('cost_center_id'))],
            'quantity' => ['required', 'numeric', 'min:0.01', 'max:999999.99', 'decimal:0,2'],
            'unit' => ['required', 'string', 'max:50'],
            'description' => ['required', 'string', 'max:1000'],
            'unit_cost' => ['required', 'numeric', 'min:0', 'max:9999999.99', 'decimal:0,2'],
            'system_type' => ['required', Rule::in(array_keys(MaintenanceBacklogItem::SYSTEMS))],
            'criticality' => ['required', Rule::in(array_keys(MaintenanceBacklogItem::CRITICALITIES))],
            'status' => ['required', Rule::in(array_keys(MaintenanceBacklogItem::STATUSES))],
        ]);
        abort_unless($this->costCenters($request)->contains('id', (int) $data['cost_center_id']), 403);
        // Multiplicar como centésimas enteras; no aceptar totales enviados por el cliente.
        $quantity = (int) round((float) $data['quantity'] * 100);
        $cost = (int) round((float) $data['unit_cost'] * 100);
        $totalCents = intdiv($quantity * $cost + 50, 100);
        $data['total_cost'] = intdiv($totalCents, 100).'.'.str_pad((string) ($totalCents % 100), 2, '0', STR_PAD_LEFT);

        return $data;
    }

    private function redirectToEquipment(array $data): RedirectResponse
    {
        return redirect()->route('maintenance.backlog.index', [
            'cost_center_id' => $data['cost_center_id'], 'vehicle_id' => $data['vehicle_id'],
        ]);
    }
}
