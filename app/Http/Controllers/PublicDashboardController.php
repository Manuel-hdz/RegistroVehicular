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
        $vehicleId = request()->filled('vehicle_id') ? (int) request('vehicle_id') : null;
        $driverId = request()->filled('driver_id') ? (int) request('driver_id') : null;

        $periods = [7, 30];
        $series = [];
        $topsVehicles = [];
        $topsKm = [];
        $topsDrivers = [];

        foreach ($periods as $pd) {
            // Serie por día
            $q = Movement::select(DB::raw('DATE(departed_at) as d'), DB::raw('COUNT(*) as total'))
                ->where('departed_at', '>=', now()->subDays($pd - 1)->startOfDay());
            if ($vehicleId) { $q->where('vehicle_id', $vehicleId); }
            if ($driverId) { $q->where('driver_id', $driverId); }
            $map = $q->groupBy(DB::raw('DATE(departed_at)'))
                ->orderBy(DB::raw('DATE(departed_at)'))
                ->pluck('total','d');
            $daysArr = collect(range(0, $pd-1))->map(fn($i)=> now()->subDays(($pd-1)-$i)->startOfDay()->toDateString());
            $series["days$pd"] = $daysArr;
            $series["values$pd"] = $daysArr->map(fn($d)=>(int)($map[$d]??0));

            // Top vehicles by departures
            $qv = Movement::select('vehicle_id', DB::raw('COUNT(*) as total'))
                ->where('departed_at','>=', now()->subDays($pd-1)->startOfDay());
            if ($driverId) { $qv->where('driver_id',$driverId); }
            if ($vehicleId) { $qv->where('vehicle_id',$vehicleId); }
            $topsVehicles[$pd] = $qv->groupBy('vehicle_id')->orderByDesc('total')->with('vehicle')->limit(5)->get();

            // Top vehicles by km
            $qkm = Movement::select('vehicle_id', DB::raw('SUM(odometer_in - odometer_out) as km'))
                ->whereNotNull('odometer_in')->where('status','closed')
                ->where('departed_at','>=', now()->subDays($pd-1)->startOfDay());
            if ($driverId) { $qkm->where('driver_id',$driverId); }
            if ($vehicleId) { $qkm->where('vehicle_id',$vehicleId); }
            $topsKm[$pd] = $qkm->groupBy('vehicle_id')->orderByDesc('km')->with('vehicle')->limit(5)->get();

            // Top drivers
            $qd = Movement::select('driver_id', DB::raw('COUNT(*) as total'))
                ->where('departed_at','>=', now()->subDays($pd-1)->startOfDay());
            if ($vehicleId) { $qd->where('vehicle_id',$vehicleId); }
            $topsDrivers[$pd] = $qd->groupBy('driver_id')->orderByDesc('total')->with('driver')->limit(5)->get();
        }

        $vehicles = Vehicle::orderBy('identifier')->orderBy('plate')->get(['id','plate','identifier']);
        $drivers = Driver::orderBy('name')->get(['id','name']);

        return view('public.dashboard', [
            'vehicles'=>$vehicles,
            'drivers'=>$drivers,
            'vehicleId'=>$vehicleId,
            'driverId'=>$driverId,
            // 7 días
            'days7'=>$series['days7'], 'series7'=>$series['values7'],
            'topVehicles7'=>$topsVehicles[7], 'topKm7'=>$topsKm[7], 'topDrivers7'=>$topsDrivers[7],
            // 30 días
            'days30'=>$series['days30'], 'series30'=>$series['values30'],
            'topVehicles30'=>$topsVehicles[30], 'topKm30'=>$topsKm[30], 'topDrivers30'=>$topsDrivers[30],
        ]);
    }
}
