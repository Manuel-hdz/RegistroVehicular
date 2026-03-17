<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;

class VehicleDestroyController extends Controller
{
    public function __invoke(Vehicle $vehicle): RedirectResponse
    {
        foreach ([$vehicle->photo_path, $vehicle->circulation_card_path, $vehicle->insurance_policy_path] as $path) {
            if (!$path) {
                continue;
            }

            if (str_starts_with($path, 'images/')) {
                $absolutePath = public_path($path);
                if (File::exists($absolutePath)) {
                    File::delete($absolutePath);
                }

                continue;
            }

            Storage::disk('local')->delete($path);
        }

        $vehicle->delete();

        return redirect()->route('vehicles.index')->with('status', 'Vehiculo eliminado.');
    }
}
