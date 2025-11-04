<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\Movement;
use App\Models\Vehicle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class MovementController extends Controller
{
    public function index(): View
    {
        $open = Movement::with(['vehicle', 'driver'])
            ->where('status', 'open')
            ->orderByDesc('departed_at')
            ->get();

        $recentClosed = Movement::with(['vehicle', 'driver'])
            ->where('status', 'closed')
            ->orderByDesc('arrived_at')
            ->limit(50)
            ->get();

        return view('movements.index', compact('open', 'recentClosed'));
    }

    public function create(): View
    {
        $vehicles = Vehicle::where('active', true)->orderBy('plate')->get();
        $drivers = Driver::where('active', true)->orderBy('name')->get();
        return view('movements.create', compact('vehicles', 'drivers'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'vehicle_id' => ['required', 'exists:vehicles,id'],
            'driver_id' => ['required', 'exists:drivers,id'],
            'odometer_out' => ['required', 'integer', 'min:0'],
            'fuel_out' => ['required', 'integer', 'min:0', 'max:100'],
            'departed_at' => ['required', 'date'],
            'destination' => ['nullable', 'string', 'max:255'],
            'notes_out' => ['nullable', 'string'],
        ]);

        $data['guard_out_id'] = Auth::id();
        $data['status'] = 'open';

        Movement::create($data);

        return redirect()->route('movements.index')->with('status', 'Salida registrada.');
    }

    public function checkinForm(Movement $movement): View
    {
        abort_unless($movement->status === 'open', 404);
        return view('movements.checkin', compact('movement'));
    }

    public function checkin(Request $request, Movement $movement): RedirectResponse
    {
        abort_unless($movement->status === 'open', 404);

        $data = $request->validate([
            'odometer_in' => ['required', 'integer', 'min:' . $movement->odometer_out],
            'fuel_in' => ['required', 'integer', 'min:0', 'max:100'],
            'arrived_at' => ['required', 'date', 'after_or_equal:' . $movement->departed_at],
            'notes_in' => ['nullable', 'string'],
        ]);

        $data['guard_in_id'] = Auth::id();
        $data['status'] = 'closed';

        $movement->update($data);

        return redirect()->route('movements.index')->with('status', 'Entrada registrada.');
    }
}

