<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VehicleController extends Controller
{
    public function index(): View
    {
        // DataTables en cliente: devolver todos los registros ordenados
        $vehicles = Vehicle::orderBy('identifier')->orderBy('plate')->get();
        return view('vehicles.index', compact('vehicles'));
    }

    public function create(): View
    {
        return view('vehicles.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'plate' => ['required', 'string', 'max:50', 'unique:vehicles,plate'],
            'vtype' => ['nullable','in:auto,pickup,furgoneta,camion'],
            'identifier' => ['nullable', 'string', 'max:100'],
            'model' => ['nullable', 'string', 'max:100'],
            'year' => ['nullable', 'integer', 'min:1900', 'max:2100'],
            'active' => ['nullable', 'boolean'],
        ]);

        $data['active'] = $request->has('active');
        Vehicle::create($data);
        return redirect()->route('vehicles.index')->with('status', 'Vehículo creado.');
    }

    public function edit(Vehicle $vehicle): View
    {
        return view('vehicles.edit', compact('vehicle'));
    }

    public function update(Request $request, Vehicle $vehicle): RedirectResponse
    {
        $data = $request->validate([
            'plate' => ['required', 'string', 'max:50', 'unique:vehicles,plate,' . $vehicle->id],
            'vtype' => ['nullable','in:auto,pickup,furgoneta,camion'],
            'identifier' => ['nullable', 'string', 'max:100'],
            'model' => ['nullable', 'string', 'max:100'],
            'year' => ['nullable', 'integer', 'min:1900', 'max:2100'],
            'active' => ['nullable', 'boolean'],
        ]);

        $data['active'] = $request->has('active');
        $vehicle->update($data);
        $page = $request->input('page');
        return redirect()->route('vehicles.index', array_filter(['page' => $page]))->with('status', 'Vehículo actualizado.');
    }
}
