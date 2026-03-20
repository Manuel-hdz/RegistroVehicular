<?php

namespace App\Http\Controllers;

use App\Models\Mechanic;
use App\Models\Personnel;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MechanicController extends Controller
{
    public function index(): View
    {
        $mechanics = Mechanic::with('personnel')->orderBy('name')->paginate(20);

        return view('mechanics.index', compact('mechanics'));
    }

    public function create(): View
    {
        return view('mechanics.create', [
            'personnelOptions' => $this->personnelOptions(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $supportsPersonnelLink = $this->supportsPersonnelLink();

        $personnelRules = ['required', 'exists:personnels,id'];
        if ($supportsPersonnelLink) {
            $personnelRules[] = Rule::unique('mechanics', 'personnel_id');
        }

        $data = $request->validate([
            'personnel_id' => $personnelRules,
            'daily_salary' => ['required', 'numeric', 'min:0'],
            'active' => ['nullable', 'boolean'],
        ]);

        $personnel = Personnel::findOrFail($data['personnel_id']);

        $data['name'] = $personnel->full_name;
        $data['active'] = $request->has('active');
        if (!$supportsPersonnelLink) {
            unset($data['personnel_id']);
        }

        Mechanic::create($data);

        return redirect()->route('mechanics.index')->with('status', 'Mecanico creado.');
    }

    public function edit(Mechanic $mechanic): View
    {
        return view('mechanics.edit', [
            'mechanic' => $mechanic,
            'personnelOptions' => $this->personnelOptions($mechanic->personnel_id),
        ]);
    }

    public function update(Request $request, Mechanic $mechanic): RedirectResponse
    {
        $supportsPersonnelLink = $this->supportsPersonnelLink();

        $personnelRules = ['required', 'exists:personnels,id'];
        if ($supportsPersonnelLink) {
            $personnelRules[] = Rule::unique('mechanics', 'personnel_id')->ignore($mechanic->id);
        }

        $data = $request->validate([
            'personnel_id' => $personnelRules,
            'daily_salary' => ['required', 'numeric', 'min:0'],
            'active' => ['nullable', 'boolean'],
        ]);

        $personnel = Personnel::findOrFail($data['personnel_id']);

        $data['name'] = $personnel->full_name;
        $data['active'] = $request->has('active');
        if (!$supportsPersonnelLink) {
            unset($data['personnel_id']);
        }

        $mechanic->update($data);

        return redirect()->route('mechanics.index')->with('status', 'Mecanico actualizado.');
    }

    private function personnelOptions(?int $selectedPersonnelId = null)
    {
        return Personnel::query()
            ->when(
                $selectedPersonnelId,
                fn ($query) => $query->where(function ($nestedQuery) use ($selectedPersonnelId) {
                    $nestedQuery->where('active', true)->orWhere('id', $selectedPersonnelId);
                }),
                fn ($query) => $query->where('active', true)
            )
            ->orderBy('first_name')
            ->orderBy('last_name')
            ->orderBy('middle_name')
            ->get();
    }

    private function supportsPersonnelLink(): bool
    {
        if (!Schema::hasTable('mechanics')) {
            return false;
        }

        try {
            DB::table('mechanics')->select('personnel_id')->limit(1)->get();

            return true;
        } catch (QueryException $exception) {
            if (($exception->errorInfo[1] ?? null) === 1054) {
                return false;
            }

            throw $exception;
        }
    }
}
