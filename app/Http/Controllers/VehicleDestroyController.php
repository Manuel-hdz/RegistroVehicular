<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\Http\RedirectResponse;

class VehicleDestroyController extends Controller
{
    public function __invoke(Vehicle $vehicle): RedirectResponse
    {
        $vehicle->delete();
        return redirect()->route('vehicles.index')->with('status', 'Vehículo eliminado.');
    }
}

