<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;

class DriverDestroyController extends Controller
{
    public function __invoke(Driver $driver): RedirectResponse
    {
        if ($driver->movements()->exists()) {
            if ($driver->active) {
                $driver->update(['active' => false]);
            }

            return redirect()
                ->route('drivers.index')
                ->with('status', 'El conductor tiene movimientos registrados. Se marcó como inactivo.');
        }

        try {
            $driver->delete();
        } catch (QueryException $exception) {
            if (($exception->errorInfo[1] ?? null) === 1451) {
                if ($driver->active) {
                    $driver->update(['active' => false]);
                }

                return redirect()
                    ->route('drivers.index')
                    ->with('status', 'El conductor tiene movimientos registrados. Se marcó como inactivo.');
            }

            throw $exception;
        }

        return redirect()->route('drivers.index')->with('status', 'Conductor eliminado.');
    }
}
