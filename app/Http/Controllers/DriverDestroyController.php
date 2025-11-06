<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use Illuminate\Http\RedirectResponse;

class DriverDestroyController extends Controller
{
    public function __invoke(Driver $driver): RedirectResponse
    {
        $driver->delete();
        return redirect()->route('drivers.index')->with('status', 'Conductor eliminado.');
    }
}

