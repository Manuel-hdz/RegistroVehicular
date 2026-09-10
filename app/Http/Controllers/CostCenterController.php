<?php

namespace App\Http\Controllers;

use App\Models\CostCenter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class CostCenterController extends Controller
{
    public function index(): View
    {
        $costCenters = CostCenter::orderBy('code')->paginate(20);

        return view('cost-centers.index', compact('costCenters'));
    }

    public function create(): View
    {
        return view('cost-centers.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:cost_centers,code'],
            'name' => ['required', 'string', 'max:150'],
            'active' => ['nullable', 'boolean'],
        ]);

        $data['active'] = $request->has('active');

        $costCenter = CostCenter::create($data);
        $costCenter->warehouseLocations()->create([
            'name' => 'General',
            'description' => 'Ubicación predeterminada',
            'active' => true,
            'created_by' => $request->user()?->id,
            'updated_by' => $request->user()?->id,
        ]);

        return redirect()->route('cost-centers.index')->with('status', 'Centro de costos creado.');
    }

    public function edit(CostCenter $costCenter): View
    {
        return view('cost-centers.edit', compact('costCenter'));
    }

    public function update(Request $request, CostCenter $costCenter): RedirectResponse
    {
        $data = $request->validate([
            'code' => [
                'required',
                'string',
                'max:50',
                Rule::unique('cost_centers', 'code')->ignore($costCenter->id),
            ],
            'name' => ['required', 'string', 'max:150'],
            'active' => ['nullable', 'boolean'],
        ]);

        $data['active'] = $request->has('active');

        $costCenter->update($data);

        return redirect()->route('cost-centers.index')->with('status', 'Centro de costos actualizado.');
    }
}
