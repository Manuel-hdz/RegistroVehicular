<?php

namespace App\Http\Controllers;

use App\Models\Mechanic;
use App\Models\Part;
use App\Models\Repair;
use App\Models\Vehicle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RepairController extends Controller
{
    public function index(Request $request): View
    {
        $query = Repair::with(['vehicle','parts','mechanics'])->orderByDesc('started_at');
        if ($request->filled('vehicle_id')) { $query->where('vehicle_id', $request->integer('vehicle_id')); }
        $repairs = $query->paginate(20)->appends($request->query());
        $vehicles = Vehicle::orderBy('identifier')->orderBy('plate')->get();
        return view('repairs.index', compact('repairs','vehicles'));
    }

    public function create(): View
    {
        $vehicles = Vehicle::orderBy('identifier')->orderBy('plate')->get();
        $parts = Part::orderBy('name')->where('active', true)->get();
        $mechanics = Mechanic::orderBy('name')->where('active', true)->get();
        return view('repairs.create', compact('vehicles','parts','mechanics'));
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'vehicle_id' => ['required','exists:vehicles,id'],
            'started_at' => ['nullable','date'],
            'duration_hours' => ['required','numeric','min:0'],
            'notes' => ['nullable','string'],
            'parts' => ['array'],
            'parts.*.id' => ['nullable','exists:parts,id'],
            'parts.*.qty' => ['nullable','integer','min:1'],
            'mechanics' => ['array'],
            'mechanics.*.id' => ['nullable','exists:mechanics,id'],
            'mechanics.*.hours' => ['nullable','numeric','min:0'],
        ]);

        $repair = Repair::create([
            'vehicle_id' => $data['vehicle_id'],
            'started_at' => $data['started_at'] ?? null,
            'duration_hours' => $data['duration_hours'],
            'notes' => $data['notes'] ?? null,
        ]);

        // Attach parts
        foreach (($data['parts'] ?? []) as $row) {
            if (!empty($row['id']) && !empty($row['qty'])) {
                $repair->parts()->attach($row['id'], ['quantity' => (int)$row['qty']]);
            }
        }
        // Attach mechanics
        foreach (($data['mechanics'] ?? []) as $row) {
            if (!empty($row['id']) && isset($row['hours'])) {
                $repair->mechanics()->attach($row['id'], ['hours' => (float)$row['hours']]);
            }
        }

        return redirect()->route('repairs.index')->with('status', 'Reparación registrada.');
    }
}

