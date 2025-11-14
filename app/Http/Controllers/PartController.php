<?php

namespace App\Http\Controllers;

use App\Models\Part;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PartController extends Controller
{
    public function index(): View
    {
        $parts = Part::orderBy('name')->paginate(20);
        return view('parts.index', compact('parts'));
    }
    public function create(): View { return view('parts.create'); }
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required','string','max:150'],
            'unit_cost' => ['required','numeric','min:0'],
            'active' => ['nullable','boolean'],
        ]);
        $data['active'] = $request->has('active');
        Part::create($data);
        return redirect()->route('parts.index')->with('status','Refacción creada.');
    }
    public function edit(Part $part): View { return view('parts.edit', compact('part')); }
    public function update(Request $request, Part $part): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required','string','max:150'],
            'unit_cost' => ['required','numeric','min:0'],
            'active' => ['nullable','boolean'],
        ]);
        $data['active'] = $request->has('active');
        $part->update($data);
        return redirect()->route('parts.index')->with('status','Refacción actualizada.');
    }
}

