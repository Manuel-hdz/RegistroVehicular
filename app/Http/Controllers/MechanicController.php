<?php

namespace App\Http\Controllers;

use App\Models\Mechanic;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MechanicController extends Controller
{
    public function index(): View
    {
        $mechanics = Mechanic::orderBy('name')->paginate(20);
        return view('mechanics.index', compact('mechanics'));
    }
    public function create(): View { return view('mechanics.create'); }
    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required','string','max:150'],
            'daily_salary' => ['required','numeric','min:0'],
            'active' => ['nullable','boolean'],
        ]);
        $data['active'] = $request->has('active');
        Mechanic::create($data);
        return redirect()->route('mechanics.index')->with('status','Mecánico creado.');
    }
    public function edit(Mechanic $mechanic): View { return view('mechanics.edit', compact('mechanic')); }
    public function update(Request $request, Mechanic $mechanic): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required','string','max:150'],
            'daily_salary' => ['required','numeric','min:0'],
            'active' => ['nullable','boolean'],
        ]);
        $data['active'] = $request->has('active');
        $mechanic->update($data);
        return redirect()->route('mechanics.index')->with('status','Mecánico actualizado.');
    }
}

