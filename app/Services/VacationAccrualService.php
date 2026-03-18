<?php

namespace App\Services;

use App\Models\Personnel;
use App\Models\VacationPolicy;
use Carbon\Carbon;

class VacationAccrualService
{
    public function sync(Personnel $personnel): Personnel
    {
        $this->applyAccrual($personnel);

        return $personnel->fresh() ?? $personnel;
    }

    public function syncLockedPersonnel(Personnel $personnel): Personnel
    {
        $this->applyAccrual($personnel);

        return $personnel;
    }

    private function applyAccrual(Personnel $personnel): void
    {
        if (!$personnel->active) {
            return;
        }

        if (!$personnel->hire_date) {
            return;
        }

        $hireDate = $personnel->hire_date->copy()->startOfDay();
        $today = Carbon::today();
        if ($hireDate->gt($today)) {
            return;
        }

        $completedYears = $hireDate->diffInYears($today);
        $awardedYears = max(0, (int) $personnel->vacation_years_awarded);

        if ($completedYears <= $awardedYears) {
            return;
        }

        $policies = VacationPolicy::where('active', true)
            ->whereIn('service_year', array_keys(VacationPolicy::fixedRanges()))
            ->orderBy('service_year')
            ->get();

        if ($policies->isEmpty()) {
            return;
        }

        $grantedDays = 0;
        for ($year = $awardedYears + 1; $year <= $completedYears; $year++) {
            $policy = $policies->where('service_year', '<=', $year)->last();
            if ($policy) {
                $grantedDays += (int) $policy->vacation_days;
            }
        }

        $personnel->pending_vacation_days = max(0, (int) $personnel->pending_vacation_days) + $grantedDays;
        $personnel->vacation_years_awarded = $completedYears;
        $personnel->save();
    }
}
