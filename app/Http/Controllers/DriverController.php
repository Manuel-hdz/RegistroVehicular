<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DriverController extends Controller
{
    public function index(): View
    {
        // DataTables en cliente: devolver todos los registros ordenados
        $drivers = Driver::orderBy('name')->get();
        return view('drivers.index', compact('drivers'));
    }

    public function create(): View
    {
        return view('drivers.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'employee_number' => ['nullable', 'string', 'max:50'],
            'license' => ['nullable', 'string', 'max:50'],
            'active' => ['nullable', 'boolean'],
        ]);

        $data['active'] = $request->has('active');
        Driver::create($data);
        return redirect()->route('drivers.index')->with('status', 'Conductor creado.');
    }

    public function edit(Driver $driver): View
    {
        return view('drivers.edit', compact('driver'));
    }

    public function update(Request $request, Driver $driver): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'employee_number' => ['nullable', 'string', 'max:50'],
            'license' => ['nullable', 'string', 'max:50'],
            'active' => ['nullable', 'boolean'],
        ]);

        $data['active'] = $request->has('active');
        $driver->update($data);
        $page = $request->input('page');
        return redirect()->route('drivers.index', array_filter(['page' => $page]))->with('status', 'Conductor actualizado.');
    }
}
