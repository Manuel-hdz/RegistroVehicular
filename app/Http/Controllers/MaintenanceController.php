<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MaintenanceController extends Controller
{
    public function index(): View
    {
        $vehicles = Vehicle::orderBy('identifier')->orderBy('plate')->get();
        $types = [
            'auto' => 'Auto',
            'pickup' => 'Pickup',
            'furgoneta' => 'Furgoneta',
            'camion' => 'Camion',
            'transporte_personal' => 'Transporte personal',
            'remolcable' => 'Remolcable',
            'equipo_pesado' => 'Equipo pesado',
            'trompo' => 'Trompo',
        ];

        return view('maintenance.index', compact('vehicles', 'types'));
    }

    public function update(Request $request, Vehicle $vehicle): RedirectResponse
    {
        $data = $request->validate([
            'availability' => ['required', 'in:available,unavailable'],
            'maintenance_note' => ['nullable', 'string', 'max:1000'],
        ]);

        $vehicle->update($data);

        return back()->with('status', 'Estado actualizado.');
    }
}
