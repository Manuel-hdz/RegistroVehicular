<?php

namespace App\Http\Controllers;

use App\Models\Personnel;
use App\Services\VacationAccrualService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class PersonnelController extends Controller
{
    public function index(Request $request): View
    {
        $personnelList = Personnel::orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        $selectedPersonnelId = (int) $request->input('personnel_id');
        if ($selectedPersonnelId <= 0 && $personnelList->isNotEmpty()) {
            $selectedPersonnelId = (int) $personnelList->first()->id;
        }

        $selectedPersonnel = $personnelList->firstWhere('id', $selectedPersonnelId);
        $emptyFields = [];

        if ($selectedPersonnel) {
            $selectedPersonnel = $this->vacationAccrualService()->sync($selectedPersonnel);

            $fieldLabels = [
                'curp' => 'CURP',
                'rfc' => 'RFC',
                'nss' => 'NSS',
                'marital_status' => 'Estado civil',
                'sex' => 'Sexo',
                'birth_date' => 'Fecha de nacimiento',
                'department' => 'Departamento',
                'position' => 'Puesto',
                'hire_date' => 'Fecha de ingreso',
                'pending_vacation_days' => 'Dias de vacaciones pendientes',
                'account_number' => 'Numero de cuenta',
                'account_type' => 'Tipo de cuenta',
                'phone' => 'Telefono',
                'email' => 'Correo',
                'address' => 'Domicilio',
                'emergency_contact_name' => 'Contacto de emergencia',
                'emergency_contact_phone' => 'Telefono de emergencia',
                'photo_path' => 'Fotografia',
            ];

            foreach ($fieldLabels as $field => $label) {
                $value = data_get($selectedPersonnel, $field);
                if (blank($value)) {
                    $emptyFields[] = $label;
                }
            }
        }

        return view('personnel.index', compact(
            'personnelList',
            'selectedPersonnelId',
            'selectedPersonnel',
            'emptyFields'
        ));
    }

    public function create(): View
    {
        return view('personnel.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validatePayload($request);
        $data['active'] = $request->has('active');
        $data['photo_path'] = $this->storePhoto($request);
        $data['terminated_at'] = $data['active'] ? null : now()->toDateString();

        $personnel = Personnel::create($data);

        return redirect()->route('personnel.index', ['personnel_id' => $personnel->id])
            ->with('status', 'Personal creado.');
    }

    public function edit(Personnel $personnel): View
    {
        return view('personnel.edit', compact('personnel'));
    }

    public function update(Request $request, Personnel $personnel): RedirectResponse
    {
        $data = $this->validatePayload($request, $personnel->id);
        $data['active'] = $request->has('active');
        $newPhotoPath = $this->storePhoto($request);
        if ($newPhotoPath) {
            $this->deletePhoto($personnel->photo_path);
            $data['photo_path'] = $newPhotoPath;
        }

        if ($data['active']) {
            $data['terminated_at'] = null;
        } else {
            $data['terminated_at'] = $personnel->terminated_at ?: now()->toDateString();
        }

        $personnel->update($data);

        return redirect()->route('personnel.index', ['personnel_id' => $personnel->id])
            ->with('status', 'Personal actualizado.');
    }

    public function deactivate(Request $request, Personnel $personnel): RedirectResponse
    {
        $request->validate([
            'terminated_at' => ['nullable', 'date'],
        ]);

        $terminatedAt = $request->input('terminated_at');
        if (!$terminatedAt) {
            $terminatedAt = now()->toDateString();
        }

        $personnel->update([
            'active' => false,
            'terminated_at' => $terminatedAt,
        ]);

        return redirect()->route('personnel.index', ['personnel_id' => $personnel->id])
            ->with('status', 'Personal dado de baja.');
    }

    public function reactivate(Personnel $personnel): RedirectResponse
    {
        $personnel->update([
            'active' => true,
            'terminated_at' => null,
        ]);

        return redirect()->route('personnel.index', ['personnel_id' => $personnel->id])
            ->with('status', 'Personal reactivado.');
    }

    public function destroy(Personnel $personnel): RedirectResponse
    {
        $this->deletePhoto($personnel->photo_path);
        $personnel->delete();

        return redirect()->route('personnel.index')->with('status', 'Personal eliminado.');
    }

    public function photo(Personnel $personnel): BinaryFileResponse
    {
        $photoPath = (string) ($personnel->photo_path ?? '');
        if ($photoPath === '') {
            abort(404);
        }

        if (str_starts_with($photoPath, 'images/')) {
            $legacyPath = public_path($photoPath);
            if (!File::exists($legacyPath)) {
                abort(404);
            }
            return response()->file($legacyPath);
        }

        if (!Storage::disk('local')->exists($photoPath)) {
            abort(404);
        }

        return response()->file(Storage::disk('local')->path($photoPath));
    }

    private function validatePayload(Request $request, ?int $personnelId = null): array
    {
        $employeeUnique = 'unique:personnels,employee_number';
        if ($personnelId) {
            $employeeUnique .= ',' . $personnelId;
        }

        return $request->validate([
            'employee_number' => ['required', 'string', 'max:50', $employeeUnique],
            'first_name' => ['required', 'string', 'max:120'],
            'last_name' => ['required', 'string', 'max:120'],
            'middle_name' => ['nullable', 'string', 'max:120'],
            'curp' => ['nullable', 'string', 'max:18'],
            'rfc' => ['nullable', 'string', 'max:13'],
            'nss' => ['nullable', 'string', 'max:20'],
            'marital_status' => ['nullable', 'string', 'max:50'],
            'sex' => ['nullable', 'string', 'max:20'],
            'birth_date' => ['nullable', 'date'],
            'department' => ['nullable', 'string', 'max:120'],
            'position' => ['nullable', 'string', 'max:120'],
            'hire_date' => ['nullable', 'date'],
            'pending_vacation_days' => ['nullable', 'integer', 'min:0', 'max:3650'],
            'account_number' => ['nullable', 'string', 'max:50'],
            'account_type' => ['nullable', 'string', 'max:50'],
            'phone' => ['nullable', 'string', 'max:40'],
            'email' => ['nullable', 'email', 'max:150'],
            'address' => ['nullable', 'string', 'max:255'],
            'emergency_contact_name' => ['nullable', 'string', 'max:150'],
            'emergency_contact_phone' => ['nullable', 'string', 'max:40'],
            'photo' => ['nullable', 'image', 'max:3072'],
            'photo_cropped' => ['nullable', 'string'],
            'active' => ['nullable', 'boolean'],
        ]);
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
            $path = 'personnel/photos/' . $fileName;
            Storage::disk('local')->put($path, $binary);

            return $path;
        }

        if (!$request->hasFile('photo')) {
            return null;
        }

        $extension = $request->file('photo')->getClientOriginalExtension();
        $fileName = Str::uuid()->toString() . '.' . $extension;
        $path = 'personnel/photos/' . $fileName;
        $request->file('photo')->storeAs('personnel/photos', $fileName, 'local');

        return $path;
    }

    private function deletePhoto(?string $photoPath): void
    {
        if (!$photoPath) {
            return;
        }

        if (str_starts_with($photoPath, 'images/')) {
            $absolutePath = public_path($photoPath);
            if (File::exists($absolutePath)) {
                File::delete($absolutePath);
            }
            return;
        }

        Storage::disk('local')->delete($photoPath);
    }

    private function vacationAccrualService(): VacationAccrualService
    {
        return app(VacationAccrualService::class);
    }
}
