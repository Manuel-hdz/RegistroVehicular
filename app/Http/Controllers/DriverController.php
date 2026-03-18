<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\Personnel;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class DriverController extends Controller
{
    public function index(): View
    {
        $drivers = Driver::with('personnel')->orderBy('name')->get();

        return view('drivers.index', compact('drivers'));
    }

    public function create(): View
    {
        return view('drivers.create', [
            'personnelOptions' => $this->personnelOptions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'personnel_id' => ['required', 'exists:personnels,id', Rule::unique('drivers', 'personnel_id')],
            'license' => ['nullable', 'string', 'max:50'],
            'active' => ['nullable', 'boolean'],
        ]);

        $personnel = Personnel::findOrFail($data['personnel_id']);

        $data['name'] = $personnel->full_name;
        $data['employee_number'] = $personnel->employee_number;
        $data['active'] = $request->has('active');

        Driver::create($data);

        return redirect()->route('drivers.index')->with('status', 'Conductor creado.');
    }

    public function edit(Driver $driver): View
    {
        return view('drivers.edit', [
            'driver' => $driver,
            'personnelOptions' => $this->personnelOptions($driver->personnel_id),
        ]);
    }

    public function update(Request $request, Driver $driver): RedirectResponse
    {
        $data = $request->validate([
            'personnel_id' => [
                'required',
                'exists:personnels,id',
                Rule::unique('drivers', 'personnel_id')->ignore($driver->id),
            ],
            'license' => ['nullable', 'string', 'max:50'],
            'active' => ['nullable', 'boolean'],
        ]);

        $personnel = Personnel::findOrFail($data['personnel_id']);

        $data['name'] = $personnel->full_name;
        $data['employee_number'] = $personnel->employee_number;
        $data['active'] = $request->has('active');

        $driver->update($data);

        $page = $request->input('page');

        return redirect()
            ->route('drivers.index', array_filter(['page' => $page]))
            ->with('status', 'Conductor actualizado.');
    }

    private function personnelOptions(?int $selectedPersonnelId = null)
    {
        return Personnel::query()
            ->when(
                $selectedPersonnelId,
                fn ($query) => $query->where(function ($nestedQuery) use ($selectedPersonnelId) {
                    $nestedQuery->where('active', true)->orWhere('id', $selectedPersonnelId);
                }),
                fn ($query) => $query->where('active', true)
            )
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->orderBy('middle_name')
            ->get();
    }
}
