<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class MaintenanceController extends Controller
{
    public function index(): View
    {
        $vehicles = Vehicle::orderBy('identifier')->orderBy('plate')->get();
        $types = ['auto' => 'Auto', 'pickup' => 'Pickup', 'camion' => 'Camión'];
        return view('maintenance.index', compact('vehicles','types'));
    }

    public function update(Request $request, Vehicle $vehicle): RedirectResponse
    {
        $data = $request->validate([
            'availability' => ['required','in:available,unavailable'],
            'maintenance_note' => ['nullable','string','max:1000'],
        ]);
        $vehicle->update($data);
        return back()->with('status', 'Estado actualizado.');
    }
}

