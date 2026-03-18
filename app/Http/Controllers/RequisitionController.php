<?php

namespace App\Http\Controllers;

use App\Models\CostCenter;
use App\Models\Part;
use App\Models\Requisition;
use App\Models\RequisitionItem;
use App\Models\Vehicle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RequisitionController extends Controller
{
    public function create(): View
    {
        return view('requisitions.create', [
            'costCenters' => CostCenter::where('active', true)->orderBy('code')->get(),
            'vehicles' => Vehicle::where('active', true)->orderBy('identifier')->orderBy('plate')->get(),
            'partSuggestions' => Part::where('active', true)->orderBy('name')->pluck('name'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'cost_center_id' => ['required', 'exists:cost_centers,id'],
            'requester_name' => ['required', 'string', 'max:150'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.material_name' => ['required', 'string', 'max:180'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.equipment_vehicle_id' => ['nullable', 'exists:vehicles,id'],
            'items.*.justification' => ['nullable', 'string', 'max:255'],
        ]);

        $requisition = DB::transaction(function () use ($data) {
            $requisition = Requisition::create([
                'cost_center_id' => $data['cost_center_id'],
                'requester_name' => $data['requester_name'],
                'vehicle_id' => null,
                'status' => Requisition::STATUS_PENDING,
            ]);

            foreach ($data['items'] as $item) {
                $requisition->items()->create([
                    'material_name' => $item['material_name'],
                    'quantity' => $item['quantity'],
                    'equipment_vehicle_id' => $item['equipment_vehicle_id'] ?? null,
                    'justification' => $item['justification'] ?? null,
                ]);
            }

            return $requisition;
        });

        return redirect()
            ->route('requisitions.create')
            ->with('status', 'Solicitud enviada correctamente. Folio: ' . $requisition->folio);
    }

    public function pending(): View
    {
        $selectedStatus = request()->string('status')->toString();
        $statuses = Requisition::statuses();

        $requisitions = Requisition::with(['costCenter', 'vehicle', 'items.equipmentVehicle'])
            ->when(
                $selectedStatus !== '' && array_key_exists($selectedStatus, $statuses),
                fn ($query) => $query->where('status', $selectedStatus)
            )
            ->orderByDesc('created_at')
            ->paginate(12)
            ->appends(request()->query());

        return view('requisitions.pending', [
            'requisitions' => $requisitions,
            'statuses' => $statuses,
            'selectedStatus' => $selectedStatus,
        ]);
    }

    public function updateStatus(Request $request, Requisition $requisition): RedirectResponse
    {
        $data = $request->validate([
            'status' => ['required', Rule::in(array_keys(Requisition::statuses()))],
        ]);

        if ($requisition->isFinalStatus()) {
            return redirect()
                ->route('requisitions.pending', array_filter([
                    'status' => $request->input('status_context'),
                ]))
                ->with('status', 'Esta requisicion ya esta cerrada y no permite mas cambios de estatus.');
        }

        $requisition->update([
            'status' => $data['status'],
        ]);

        return redirect()
            ->route('requisitions.pending', array_filter([
                'status' => $request->input('status_context'),
            ]))
            ->with('status', 'Estatus actualizado correctamente.');
    }

    public function updateItemChecks(Request $request, RequisitionItem $requisitionItem): RedirectResponse
    {
        $data = $request->validate([
            'field' => ['required', Rule::in(['is_ordered', 'is_in_storage'])],
            'value' => ['required', 'boolean'],
        ]);

        $requisitionItem->update([
            $data['field'] => (bool) $data['value'],
        ]);

        return redirect()
            ->route('requisitions.pending', array_filter([
                'status' => $request->input('status_context'),
            ]))
            ->with('status', 'Verificacion del material actualizada correctamente.');
    }
}
