<?php

namespace App\Http\Controllers;

use App\Models\Movement;
use App\Models\Vehicle;
use App\Models\Driver;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class PublicDashboardController extends Controller
{
    public function index(): View
    {
        $daysParam = (int) request('days', 30);
        $allowed = [7, 30, 90];
        $selectedDays = in_array($daysParam, $allowed, true) ? $daysParam : 30;
        $vehicleId = request()->filled('vehicle_id') ? (int) request('vehicle_id') : null;
        $driverId = request()->filled('driver_id') ? (int) request('driver_id') : null;

        // Serie temporal últimos 30 días: salidas por día
        $byDayQuery = Movement::select(DB::raw('DATE(departed_at) as d'), DB::raw('COUNT(*) as total'))
            ->where('departed_at', '>=', now()->subDays($selectedDays - 1)->startOfDay());
        if ($vehicleId) { $byDayQuery->where('vehicle_id', $vehicleId); }
        if ($driverId) { $byDayQuery->where('driver_id', $driverId); }
        $byDay = $byDayQuery
            ->groupBy(DB::raw('DATE(departed_at)'))
            ->orderBy(DB::raw('DATE(departed_at)'))
            ->pluck('total', 'd');
        $days = collect(range(0, $selectedDays - 1))->map(fn($i) => now()->subDays(($selectedDays - 1) - $i)->startOfDay()->toDateString());
        $seriesDays = $days->map(fn($d) => (int) ($byDay[$d] ?? 0));

        // Equipo que sale más (conteo de salidas) - Top 5
        $topVehicleByDeparturesQuery = Movement::select('vehicle_id', DB::raw('COUNT(*) as total'))
            ->where('departed_at', '>=', now()->subDays($selectedDays - 1)->startOfDay());
        if ($driverId) { $topVehicleByDeparturesQuery->where('driver_id', $driverId); }
        $topVehicleByDepartures = $topVehicleByDeparturesQuery
            ->groupBy('vehicle_id')
            ->orderByDesc('total')
            ->with('vehicle')
            ->limit(5)
            ->get();

        // Equipo con más kilómetros (solo cerrados y con odómetro de entrada)
        $topVehicleByKmQuery = Movement::select('vehicle_id', DB::raw('SUM(odometer_in - odometer_out) as km'))
            ->whereNotNull('odometer_in')
            ->where('status', 'closed')
            ->where('departed_at', '>=', now()->subDays($selectedDays - 1)->startOfDay());
        if ($driverId) { $topVehicleByKmQuery->where('driver_id', $driverId); }
        $topVehicleByKm = $topVehicleByKmQuery
            ->groupBy('vehicle_id')
            ->orderByDesc('km')
            ->with('vehicle')
            ->limit(5)
            ->get();

        // Conductor que sale más
        $topDriverByDeparturesQuery = Movement::select('driver_id', DB::raw('COUNT(*) as total'))
            ->where('departed_at', '>=', now()->subDays($selectedDays - 1)->startOfDay());
        if ($vehicleId) { $topDriverByDeparturesQuery->where('vehicle_id', $vehicleId); }
        $topDriverByDepartures = $topDriverByDeparturesQuery
            ->groupBy('driver_id')
            ->orderByDesc('total')
            ->with('driver')
            ->limit(5)
            ->get();

        $vehicles = Vehicle::orderBy('identifier')->orderBy('plate')->get(['id','plate','identifier']);
        $drivers = Driver::orderBy('name')->get(['id','name']);

        return view('public.dashboard', compact(
            'days', 'seriesDays',
            'topVehicleByDepartures',
            'topVehicleByKm',
            'topDriverByDepartures',
            'selectedDays',
            'vehicles','drivers','vehicleId','driverId'
        ));
    }
}
