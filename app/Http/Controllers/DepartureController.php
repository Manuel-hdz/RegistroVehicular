<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use App\Models\Movement;
use App\Models\Vehicle;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Illuminate\View\View;

class DepartureController extends Controller
{
    public function index(Request $request): View
    {
        $query = Movement::with(['vehicle','driver','guardOut'])
            ->orderByDesc('departed_at');

        if ($request->filled('date_from')) {
            $query->where('departed_at', '>=', $request->date('date_from')->startOfDay());
        }
        if ($request->filled('date_to')) {
            $query->where('departed_at', '<=', $request->date('date_to')->endOfDay());
        }
        if ($request->filled('vehicle_id')) {
            $query->where('vehicle_id', $request->integer('vehicle_id'));
        }
        if ($request->filled('driver_id')) {
            $query->where('driver_id', $request->integer('driver_id'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }
        if ($request->filled('destination')) {
            $query->where('destination', 'like', '%' . $request->string('destination') . '%');
        }

        // Para DataTables en cliente: regresamos colección completa ya filtrada
        $departures = $query->get();

        $vehicles = Vehicle::orderBy('identifier')->orderBy('plate')->get(['id','plate','identifier']);
        $drivers = Driver::orderBy('name')->get(['id','name']);

        $statuses = [
            '' => 'Todos',
            'open' => 'Abierto',
            'closed' => 'Cerrado',
            'cancelled' => 'Cancelado',
        ];

        return view('departures.index', compact('departures','vehicles','drivers','statuses'));
    }

    public function export(Request $request): StreamedResponse
    {
        $query = Movement::with(['vehicle','driver','guardOut'])
            ->orderByDesc('departed_at');

        if ($request->filled('date_from')) {
            $query->where('departed_at', '>=', $request->date('date_from')->startOfDay());
        }
        if ($request->filled('date_to')) {
            $query->where('departed_at', '<=', $request->date('date_to')->endOfDay());
        }
        if ($request->filled('vehicle_id')) {
            $query->where('vehicle_id', $request->integer('vehicle_id'));
        }
        if ($request->filled('driver_id')) {
            $query->where('driver_id', $request->integer('driver_id'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }
        if ($request->filled('destination')) {
            $query->where('destination', 'like', '%' . $request->string('destination') . '%');
        }

        $rows = $query->get();

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="salidas.csv"',
        ];

        $callback = function () use ($rows) {
            $out = fopen('php://output', 'w');
            fputcsv($out, ['#','Fecha/Hora Salida','Vehículo','Conductor','Registró','Estatus','Destino','Odómetro','Combustible%']);
            foreach ($rows as $m) {
                $status = match ($m->status) {
                    'closed' => 'Completado',
                    'cancelled' => 'Cancelado',
                    default => 'Abierto',
                };
                fputcsv($out, [
                    $m->id,
                    optional($m->departed_at)->format('Y-m-d H:i'),
                    optional($m->vehicle)->identifier,
                    optional($m->driver)->name,
                    optional($m->guardOut)->name,
                    $status,
                    $m->destination,
                    $m->odometer_out,
                    $m->fuel_out,
                ]);
            }
            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }

    public function exportExcel(Request $request): StreamedResponse
    {
        $query = Movement::with(['vehicle','driver','guardOut'])
            ->orderByDesc('departed_at');

        if ($request->filled('date_from')) {
            $query->where('departed_at', '>=', $request->date('date_from')->startOfDay());
        }
        if ($request->filled('date_to')) {
            $query->where('departed_at', '<=', $request->date('date_to')->endOfDay());
        }
        if ($request->filled('vehicle_id')) {
            $query->where('vehicle_id', $request->integer('vehicle_id'));
        }
        if ($request->filled('driver_id')) {
            $query->where('driver_id', $request->integer('driver_id'));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->string('status'));
        }
        if ($request->filled('destination')) {
            $query->where('destination', 'like', '%' . $request->string('destination') . '%');
        }

        $rows = $query->get();

        $headers = [
            'Content-Type' => 'application/vnd.ms-excel; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="salidas.xls"',
        ];

        $callback = function () use ($rows) {
            $out = fopen('php://output', 'w');
            // BOM para UTF-8
            fwrite($out, "\xEF\xBB\xBF");
            // HTML básico que Excel interpreta como hoja
            fwrite($out, "<html><head><meta charset=\"UTF-8\"></head><body>");
            fwrite($out, "<table border=\"1\" cellspacing=\"0\" cellpadding=\"2\">\n");
            fwrite($out, "<tr><th># Operación</th><th>Fecha/Hora Salida</th><th>Vehículo</th><th>Conductor</th><th>Registró</th><th>Estatus</th><th>Destino</th><th>Odómetro</th><th>Combustible%</th></tr>\n");
            foreach ($rows as $m) {
                $status = match ($m->status) {
                    'closed' => 'Completado',
                    'cancelled' => 'Cancelado',
                    default => 'Abierto',
                };
                $cells = [
                    e((string)$m->id),
                    e(optional($m->departed_at)->format('Y-m-d H:i')),
                    e(optional($m->vehicle)->identifier),
                    e(optional($m->driver)->name),
                    e(optional($m->guardOut)->name),
                    e($status),
                    e($m->destination),
                    e((string)$m->odometer_out),
                    e((string)$m->fuel_out),
                ];
                fwrite($out, '<tr><td>'.implode('</td><td>', $cells)."</td></tr>\n");
            }
            fwrite($out, "</table></body></html>");
            fclose($out);
        };

        return response()->stream($callback, 200, $headers);
    }
}
