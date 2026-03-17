<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class VehicleController extends Controller
{
    public function index(Request $request): View
    {
        $vehicles = Vehicle::orderBy('identifier')->orderBy('plate')->get();

        $selectedVehicleId = (int) $request->input('vehicle_id');
        if ($selectedVehicleId <= 0 && $vehicles->isNotEmpty()) {
            $selectedVehicleId = (int) $vehicles->first()->id;
        }

        $selectedVehicle = $vehicles->firstWhere('id', $selectedVehicleId);
        $emptyFields = [];

        if ($selectedVehicle) {
            $fieldLabels = [
                'plate' => 'Placa',
                'identifier' => 'Identificador',
                'serial_number' => 'Numero de serie',
                'additional_serial_number' => 'Numero de serie adicional',
                'engine_number' => 'Motor',
                'supplier' => 'Proveedor',
                'assigned_personnel' => 'Personal asignado',
                'model' => 'Modelo',
                'year' => 'Anio',
                'description' => 'Descripcion',
                'photo_path' => 'Foto del equipo',
                'circulation_card_path' => 'Tarjeta de circulacion',
                'insurance_policy_path' => 'Poliza de seguro',
            ];

            foreach ($fieldLabels as $field => $label) {
                if (blank(data_get($selectedVehicle, $field))) {
                    $emptyFields[] = $label;
                }
            }
        }

        return view('vehicles.index', [
            'vehicles' => $vehicles,
            'selectedVehicleId' => $selectedVehicleId,
            'selectedVehicle' => $selectedVehicle,
            'emptyFields' => $emptyFields,
            'typeLabels' => $this->typeLabels(),
        ]);
    }

    public function create(): View
    {
        return view('vehicles.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatePayload($request);
        $data['photo_path'] = $this->storeDocument($request, 'photo', 'photo');
        $data['circulation_card_path'] = $this->storeDocument($request, 'circulation_card', 'circulation-card');
        $data['insurance_policy_path'] = $this->storeDocument($request, 'insurance_policy', 'insurance-policy');
        $data['active'] = $this->resolveActiveFlag($request, true);

        $vehicle = Vehicle::create($data);

        return redirect()->route('vehicles.index', ['vehicle_id' => $vehicle->id])->with('status', 'Vehiculo creado.');
    }

    public function edit(Vehicle $vehicle): View
    {
        return view('vehicles.edit', compact('vehicle'));
    }

    public function update(Request $request, Vehicle $vehicle): RedirectResponse
    {
        $data = $this->validatePayload($request, $vehicle->id);
        $data['active'] = $this->resolveActiveFlag($request, $vehicle->active);

        $photoPath = $this->storeDocument($request, 'photo', 'photo');
        if ($photoPath) {
            $this->deleteDocument($vehicle->photo_path);
            $data['photo_path'] = $photoPath;
        }

        $circulationCardPath = $this->storeDocument($request, 'circulation_card', 'circulation-card');
        if ($circulationCardPath) {
            $this->deleteDocument($vehicle->circulation_card_path);
            $data['circulation_card_path'] = $circulationCardPath;
        }

        $insurancePolicyPath = $this->storeDocument($request, 'insurance_policy', 'insurance-policy');
        if ($insurancePolicyPath) {
            $this->deleteDocument($vehicle->insurance_policy_path);
            $data['insurance_policy_path'] = $insurancePolicyPath;
        }

        $vehicle->update($data);
        $page = $request->input('page');

        return redirect()->route('vehicles.index', array_filter([
            'page' => $page,
            'vehicle_id' => $vehicle->id,
        ]))->with('status', 'Vehiculo actualizado.');
    }

    public function document(Vehicle $vehicle, string $document): BinaryFileResponse
    {
        $path = match ($document) {
            'photo' => $vehicle->photo_path,
            'circulation-card' => $vehicle->circulation_card_path,
            'insurance-policy' => $vehicle->insurance_policy_path,
            default => null,
        };

        if (!$path) {
            abort(404);
        }

        if (str_starts_with($path, 'images/')) {
            $legacyPath = public_path($path);
            if (!File::exists($legacyPath)) {
                abort(404);
            }

            return response()->file($legacyPath);
        }

        if (!Storage::disk('local')->exists($path)) {
            abort(404);
        }

        return response()->file(Storage::disk('local')->path($path));
    }

    private function validatePayload(Request $request, ?int $vehicleId = null): array
    {
        $plateUnique = 'unique:vehicles,plate';
        if ($vehicleId) {
            $plateUnique .= ',' . $vehicleId;
        }

        return $request->validate([
            'plate' => ['required', 'string', 'max:50', $plateUnique],
            'vtype' => ['nullable', 'in:auto,pickup,furgoneta,camion,transporte_personal,remolcable,equipo_pesado,trompo'],
            'identifier' => ['nullable', 'string', 'max:100'],
            'model' => ['nullable', 'string', 'max:100'],
            'year' => ['nullable', 'integer', 'min:1900', 'max:2100'],
            'serial_number' => ['nullable', 'string', 'max:120'],
            'additional_serial_number' => ['nullable', 'string', 'max:120'],
            'engine_number' => ['nullable', 'string', 'max:120'],
            'supplier' => ['nullable', 'string', 'max:150'],
            'assigned_personnel' => ['nullable', 'string', 'max:150'],
            'description' => ['nullable', 'string', 'max:2000'],
            'photo' => ['nullable', 'image', 'max:5120'],
            'circulation_card' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'insurance_policy' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'active' => ['nullable', 'boolean'],
        ]);
    }

    private function resolveActiveFlag(Request $request, bool $currentValue): bool
    {
        if (!auth()->user() || auth()->user()->role !== 'superadmin') {
            return $currentValue;
        }

        return $request->has('active');
    }

    private function storeDocument(Request $request, string $field, string $prefix): ?string
    {
        if (!$request->hasFile($field)) {
            return null;
        }

        $extension = $request->file($field)->getClientOriginalExtension();
        $fileName = $prefix . '-' . Str::uuid()->toString() . '.' . $extension;
        $request->file($field)->storeAs('vehicles/documents', $fileName, 'local');

        return 'vehicles/documents/' . $fileName;
    }

    private function deleteDocument(?string $path): void
    {
        if (!$path) {
            return;
        }

        if (str_starts_with($path, 'images/')) {
            $absolutePath = public_path($path);
            if (File::exists($absolutePath)) {
                File::delete($absolutePath);
            }

            return;
        }

        Storage::disk('local')->delete($path);
    }

    private function typeLabels(): array
    {
        return [
            'auto' => 'Auto',
            'pickup' => 'Pickup',
            'furgoneta' => 'Furgoneta',
            'camion' => 'Camion',
            'transporte_personal' => 'Transporte personal',
            'remolcable' => 'Remolcable',
            'equipo_pesado' => 'Equipo pesado',
            'trompo' => 'Trompo',
        ];
    }
}
