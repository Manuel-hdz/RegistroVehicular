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
        $vehicles = Vehicle::orderBy('identifier')->orderBy('unit_name')->orderBy('plate')->get();

        $selectedVehicleId = (int) $request->input('vehicle_id');
        if ($selectedVehicleId <= 0 && $vehicles->isNotEmpty()) {
            $selectedVehicleId = (int) $vehicles->first()->id;
        }

        $selectedVehicle = $vehicles->firstWhere('id', $selectedVehicleId);
        $emptyFields = [];

        if ($selectedVehicle) {
            $fieldLabels = [
                'identifier' => 'Clave',
                'unit_name' => 'Nombre de unidad',
                'registration_date' => 'Fecha de alta',
                'make_model' => 'Marca/modelo',
                'model' => 'Modelo',
                'plate' => 'Placa',
                'serial_number' => 'Numero de serie',
                'additional_serial_number' => 'Serie eq. adicional',
                'tenure_path' => 'Tenencia',
                'circulation_card_path' => 'Tarjeta de circulacion',
                'engine_type' => 'Tipo de motor',
                'engine_filters' => 'Filtros de motor',
                'area' => 'Area',
                'family' => 'Familia',
                'manufacture_date' => 'Fecha de fabricacion',
                'assigned_personnel' => 'Asignado',
                'equipment_status' => 'Estado',
                'supplier' => 'Proveedor',
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
        $data['photo_path'] = $this->storePhoto($request);
        $data['circulation_card_path'] = $this->storeDocument($request, 'circulation_card', 'circulation-card');
        $data['tenure_path'] = $this->storeDocument($request, 'tenure', 'tenure');
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

        $photoPath = $this->storePhoto($request);
        if ($photoPath) {
            $this->deleteDocument($vehicle->photo_path);
            $data['photo_path'] = $photoPath;
        }

        $circulationCardPath = $this->storeDocument($request, 'circulation_card', 'circulation-card');
        if ($circulationCardPath) {
            $this->deleteDocument($vehicle->circulation_card_path);
            $data['circulation_card_path'] = $circulationCardPath;
        }

        $tenurePath = $this->storeDocument($request, 'tenure', 'tenure');
        if ($tenurePath) {
            $this->deleteDocument($vehicle->tenure_path);
            $data['tenure_path'] = $tenurePath;
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
            'tenure' => $vehicle->tenure_path,
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
            'identifier' => ['nullable', 'string', 'max:100'],
            'unit_name' => ['nullable', 'string', 'max:150'],
            'registration_date' => ['nullable', 'date'],
            'make_model' => ['nullable', 'string', 'max:150'],
            'model' => ['nullable', 'string', 'max:100'],
            'plate' => ['nullable', 'string', 'max:50', $plateUnique],
            'serial_number' => ['nullable', 'string', 'max:120'],
            'additional_serial_number' => ['nullable', 'string', 'max:120'],
            'engine_type' => ['nullable', 'string', 'max:120'],
            'engine_filters' => ['nullable', 'string', 'max:2000'],
            'area' => ['nullable', 'string', 'max:120'],
            'family' => ['nullable', 'string', 'max:120'],
            'manufacture_date' => ['nullable', 'date'],
            'assigned_personnel' => ['nullable', 'string', 'max:150'],
            'equipment_status' => ['nullable', 'string', 'max:100'],
            'supplier' => ['nullable', 'string', 'max:150'],
            'photo' => ['nullable', 'image', 'max:5120'],
            'photo_cropped' => ['nullable', 'string'],
            'tenure' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'circulation_card' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
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

    private function storePhoto(Request $request): ?string
    {
        $croppedImage = (string) $request->input('photo_cropped', '');
        if ($croppedImage !== '') {
            if (!preg_match('/^data:image\/([a-zA-Z0-9.+-]+);base64,/', $croppedImage, $matches)) {
                return null;
            }

            $extension = strtolower($matches[1]);
            if ($extension === 'jpeg') {
                $extension = 'jpg';
            }

            $binary = base64_decode(substr($croppedImage, strpos($croppedImage, ',') + 1), true);
            if ($binary === false) {
                return null;
            }

            $fileName = 'photo-' . Str::uuid()->toString() . '.' . $extension;
            Storage::disk('local')->put('vehicles/photos/' . $fileName, $binary);

            return 'vehicles/photos/' . $fileName;
        }

        return $this->storeDocument($request, 'photo', 'photo');
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
