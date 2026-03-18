<?php

namespace App\Http\Controllers;

use App\Models\VacationPolicy;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class VacationPolicyController extends Controller
{
    public function index(): View
    {
        $vacationPolicies = $this->ensureFixedPolicies();

        return view('vacation-policies.index', compact('vacationPolicies'));
    }

    public function updateTable(Request $request): RedirectResponse
    {
        $rules = [];
        foreach (array_keys(VacationPolicy::fixedRanges()) as $serviceYear) {
            $rules['vacation_days.' . $serviceYear] = ['required', 'integer', 'min:0', 'max:365'];
        }

        $data = $request->validate($rules, [
            'vacation_days.*.required' => 'Todos los rangos deben tener dias configurados.',
        ]);

        $policies = $this->ensureFixedPolicies();

        foreach ($policies as $policy) {
            $policy->update([
                'vacation_days' => (int) data_get($data, 'vacation_days.' . $policy->service_year, 0),
                'active' => true,
            ]);
        }

        return redirect()->route('vacation-policies.index')->with('status', 'Tabla de vacaciones actualizada.');
    }

    /**
     * @return \Illuminate\Support\Collection<int, VacationPolicy>
     */
    private function ensureFixedPolicies()
    {
        foreach (VacationPolicy::fixedRanges() as $serviceYear => $label) {
            VacationPolicy::firstOrCreate(
                ['service_year' => $serviceYear],
                ['vacation_days' => 0, 'notes' => $label, 'active' => true]
            );
        }

        return VacationPolicy::whereIn('service_year', array_keys(VacationPolicy::fixedRanges()))
            ->orderBy('service_year')
            ->get();
    }
}
