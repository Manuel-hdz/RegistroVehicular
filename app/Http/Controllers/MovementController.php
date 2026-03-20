<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\Movement;
use App\Models\User;
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
        $vehicles = Vehicle::where('active', true)->orderBy('identifier')->orderBy('plate')->get();
        $drivers = Driver::where('active', true)->orderBy('name')->get();
        $lastOdometers = Movement::orderByDesc('departed_at')
            ->orderByDesc('id')
            ->get()
            ->unique('vehicle_id')
            ->mapWithKeys(function ($m) {
                // prioriza lectura más reciente, usa odómetro de salida si no hay entrada
                $reading = $m->odometer_in ?? $m->odometer_out;
                return [$m->vehicle_id => $reading];
            })
            ->toArray();

        return view('movements.create', compact('vehicles', 'drivers', 'lastOdometers'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'vehicle_id' => ['required', 'exists:vehicles,id'],
            'driver_id' => ['required', 'exists:drivers,id'],
            'odometer_out' => ['required', 'integer', 'min:0'],
            // combustible vía medida fraccional
            'fuel_out_base' => ['required', 'in:reserve,1/4,1/2,3/4,1'],
            'fuel_out_dir' => ['required_unless:fuel_out_base,reserve', 'in:below,exact,above', 'nullable'],
            'departed_at' => ['required', 'date'],
            'destination' => ['nullable', 'string', 'max:255'],
            'notes_out' => ['nullable', 'string'],
        ]);

        if ($data['fuel_out_base'] === 'reserve' && empty($data['fuel_out_dir'])) {
            $data['fuel_out_dir'] = 'exact';
        }
        $data['fuel_out'] = $this->fuelToPercent($data['fuel_out_base'], $data['fuel_out_dir']);
        unset($data['fuel_out_base'], $data['fuel_out_dir']);

        $data['guard_out_id'] = $this->resolveGuardUserId();
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
            'fuel_in_base' => ['required', 'in:reserve,1/4,1/2,3/4,1'],
            'fuel_in_dir' => ['required_unless:fuel_in_base,reserve', 'in:below,exact,above', 'nullable'],
            'arrived_at' => ['required', 'date', 'after_or_equal:' . $movement->departed_at],
            'notes_in' => ['nullable', 'string'],
        ]);

        if ($data['fuel_in_base'] === 'reserve' && empty($data['fuel_in_dir'])) {
            $data['fuel_in_dir'] = 'exact';
        }
        $data['fuel_in'] = $this->fuelToPercent($data['fuel_in_base'], $data['fuel_in_dir']);
        unset($data['fuel_in_base'], $data['fuel_in_dir']);

        $data['guard_in_id'] = $this->resolveGuardUserId();
        $data['status'] = 'closed';

        $movement->update($data);

        return redirect()->route('movements.index')->with('status', 'Entrada registrada.');
    }

    // Solo SuperAdmin
    public function edit(Movement $movement): View
    {
        if (!Auth::user() || Auth::user()->role !== 'superadmin') {
            abort(403);
        }
        $vehicles = Vehicle::orderBy('identifier')->orderBy('plate')->get();
        $drivers = Driver::orderBy('name')->get();
        return view('movements.edit', compact('movement','vehicles','drivers'));
    }

    public function update(Request $request, Movement $movement): RedirectResponse
    {
        if (!Auth::user() || Auth::user()->role !== 'superadmin') {
            abort(403);
        }
        $data = $request->validate([
            'vehicle_id' => ['required','exists:vehicles,id'],
            'driver_id' => ['required','exists:drivers,id'],
            'odometer_out' => ['required','integer','min:0'],
            'fuel_out' => ['nullable','integer','min:0','max:100'],
            'fuel_out_base' => ['nullable','in:reserve,1/4,1/2,3/4,1'],
            'fuel_out_dir' => ['nullable','in:below,exact,above'],
            'departed_at' => ['required','date'],
            'destination' => ['nullable','string','max:255'],
            'notes_out' => ['nullable','string'],
            'odometer_in' => ['nullable','integer','min:0'],
            'fuel_in' => ['nullable','integer','min:0','max:100'],
            'fuel_in_base' => ['nullable','in:reserve,1/4,1/2,3/4,1'],
            'fuel_in_dir' => ['nullable','in:below,exact,above'],
            'arrived_at' => ['nullable','date'],
            'notes_in' => ['nullable','string'],
            'status' => ['required','string'],
        ]);
        if (!empty($data['fuel_out_base']) && (!empty($data['fuel_out_dir']) || $data['fuel_out_base'] === 'reserve')) {
            $data['fuel_out'] = $this->fuelToPercent($data['fuel_out_base'], $data['fuel_out_dir'] ?? 'exact');
        }
        unset($data['fuel_out_base'], $data['fuel_out_dir']);

        if (!empty($data['fuel_in_base']) && (!empty($data['fuel_in_dir']) || $data['fuel_in_base'] === 'reserve')) {
            $data['fuel_in'] = $this->fuelToPercent($data['fuel_in_base'], $data['fuel_in_dir'] ?? 'exact');
        }
        unset($data['fuel_in_base'], $data['fuel_in_dir']);

        $movement->update($data);
        return redirect()->route('movements.index')->with('status', 'Movimiento actualizado.');
    }

    public function cancel(Movement $movement): RedirectResponse
    {
        if (!Auth::user() || Auth::user()->role !== 'superadmin') {
            abort(403);
        }
        if ($movement->status !== 'open') {
            return back()->withErrors(['status' => 'Solo se pueden cancelar salidas abiertas.']);
        }
        $movement->update(['status' => 'cancelled']);
        return redirect()->route('movements.index')->with('status', 'Salida cancelada.');
    }
    private function fuelToPercent(string $base, string $dir): int
    {
        $map = [
            'reserve' => 5,
            '1/4' => 25,
            '1/2' => 50,
            '3/4' => 75,
            '1' => 100,
        ];
        $pct = $map[$base] ?? 50;
        $delta = 10; // ajuste aproximado
        if ($base !== 'reserve') {
            if ($dir === 'above') {
                $pct = min(100, $pct + $delta);
            } elseif ($dir === 'below') {
                $pct = max(0, $pct - $delta);
            }
        }
        return (int) $pct;
    }

    private function resolveGuardUserId(): ?int
    {
        $user = Auth::user();
        if (!$user) {
            return null;
        }

        // Los usuarios operativos pueden registrar salidas sin quedar como guardia en la FK.
        if ($user->role === 'user') {
            return null;
        }

        return User::query()->whereKey($user->id)->exists() ? (int) $user->id : null;
    }
}
