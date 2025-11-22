<?php

namespace App\Http\Controllers;

use App\Models\Movement;
use App\Models\Vehicle;
use App\Models\Driver;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class GraphController extends Controller
{
    private function ensureJpGraph(): bool
    {
        if (class_exists('Graph')) {
            return true;
        }
        foreach ([
            base_path('vendor/jpgraph/jpgraph/src/jpgraph.php'),
            base_path('vendor/amenadiel/jpgraph/src/jpgraph.php'),
            base_path('vendor/jpgraph/jpgraph.php'),
        ] as $file) {
            if (file_exists($file)) {
                require_once $file;
                @require_once dirname($file) . '/jpgraph_line.php';
                @require_once dirname($file) . '/jpgraph_bar.php';
                return true;
            }
        }
        return class_exists('Graph');
    }

    private function pngResponseFromGD($width, $height, $text = 'Grafica no disponible')
    {
        $im = imagecreatetruecolor($width, $height);
        $white = imagecolorallocate($im, 255, 255, 255);
        $grey = imagecolorallocate($im, 120, 120, 120);
        imagefilledrectangle($im, 0, 0, $width, $height, $white);
        imagestring($im, 4, 10, 10, $text, $grey);
        ob_start();
        imagepng($im);
        $png = ob_get_clean();
        imagedestroy($im);
        return response($png, 200)->header('Content-Type', 'image/png');
    }

    public function byDay(Request $request): Response
    {
        $ok = $this->ensureJpGraph();
        $daysParam = (int) $request->query('days', 30);
        $allowed = [7, 30, 90];
        $selectedDays = in_array($daysParam, $allowed, true) ? $daysParam : 30;
        $vehicleId = $request->filled('vehicle_id') ? (int) $request->query('vehicle_id') : null;
        $driverId = $request->filled('driver_id') ? (int) $request->query('driver_id') : null;

        $byDayQuery = Movement::select(DB::raw('DATE(departed_at) as d'), DB::raw('COUNT(*) as total'))
            ->where('departed_at', '>=', now()->subDays($selectedDays - 1)->startOfDay());
        if ($vehicleId) { $byDayQuery->where('vehicle_id', $vehicleId); }
        if ($driverId) { $byDayQuery->where('driver_id', $driverId); }
        $byDay = $byDayQuery->groupBy(DB::raw('DATE(departed_at)'))
            ->orderBy(DB::raw('DATE(departed_at)'))
            ->pluck('total', 'd');
        $labels = collect(range(0, $selectedDays - 1))
            ->map(fn($i) => now()->subDays(($selectedDays - 1) - $i)->startOfDay()->toDateString());
        $values = $labels->map(fn($d) => (int) ($byDay[$d] ?? 0))->all();

        if (!$ok) {
            return $this->pngResponseFromGD(760, 240, 'Instale jpgraph para ver graficas');
        }

        // JpGraph line chart
        $graph = new \Graph(760, 240);
        $graph->SetScale('textlin');
        $graph->img->SetMargin(50,20,20,60);
        $graph->title->Set('Salidas por dia');
        $graph->xaxis->SetTickLabels($labels->map(fn($d)=>substr($d,5))->all());
        $graph->xaxis->SetLabelAngle(50);
        $p1 = new \LinePlot($values);
        $p1->SetColor('#006847');
        $p1->SetFillColor('#cdece3');
        $p1->SetWeight(2);
        $graph->Add($p1);
        ob_start();
        $graph->Stroke();
        $png = ob_get_clean();
        return response($png, 200)->header('Content-Type', 'image/png');
    }

    public function topVehiclesDepartures(Request $request): Response
    {
        $ok = $this->ensureJpGraph();
        $days = (int) $request->query('days', 30);
        $driverId = $request->filled('driver_id') ? (int) $request->query('driver_id') : null;
        $vehicleId = $request->filled('vehicle_id') ? (int) $request->query('vehicle_id') : null;
        $q = Movement::select('vehicle_id', DB::raw('COUNT(*) as total'))
            ->where('departed_at', '>=', now()->subDays(($days>0?$days:30) - 1)->startOfDay());
        if ($driverId) { $q->where('driver_id', $driverId); }
        if ($vehicleId) { $q->where('vehicle_id', $vehicleId); }
        $rows = $q->groupBy('vehicle_id')->orderByDesc('total')->limit(5)->get();
        $labels = $rows->map(function($r){
            $v = Vehicle::find($r->vehicle_id);
            return $v?->identifier ?? ('#'.$r->vehicle_id);
        })->all();
        $values = $rows->pluck('total')->map(fn($x)=>(int)$x)->all();
        if (!$ok) { return $this->pngResponseFromGD(520, 260, 'Instale jpgraph'); }
        $graph = new \Graph(520, 260);
        $graph->SetScale('textlin');
        $graph->img->SetMargin(80,20,20,60);
        $graph->title->Set('Top equipos por salidas');
        $graph->xaxis->SetTickLabels($labels);
        $graph->xaxis->SetLabelAngle(20);
        $bar = new \BarPlot($values);
        $bar->SetFillColor('#FFCD11');
        $graph->Add($bar);
        ob_start(); $graph->Stroke(); $png = ob_get_clean();
        return response($png, 200)->header('Content-Type', 'image/png');
    }

    public function topVehiclesKm(Request $request): Response
    {
        $ok = $this->ensureJpGraph();
        $days = (int) $request->query('days', 30);
        $driverId = $request->filled('driver_id') ? (int) $request->query('driver_id') : null;
        $vehicleId = $request->filled('vehicle_id') ? (int) $request->query('vehicle_id') : null;
        $q = Movement::select('vehicle_id', DB::raw('SUM(odometer_in - odometer_out) as km'))
            ->whereNotNull('odometer_in')
            ->where('status', 'closed')
            ->where('departed_at', '>=', now()->subDays(($days>0?$days:30) - 1)->startOfDay());
        if ($driverId) { $q->where('driver_id', $driverId); }
        if ($vehicleId) { $q->where('vehicle_id', $vehicleId); }
        $rows = $q->groupBy('vehicle_id')->orderByDesc('km')->limit(5)->get();
        $labels = $rows->map(function($r){
            $v = Vehicle::find($r->vehicle_id);
            return $v?->identifier ?? ('#'.$r->vehicle_id);
        })->all();
        $values = $rows->pluck('km')->map(fn($x)=>(int)$x)->all();
        if (!$ok) { return $this->pngResponseFromGD(520, 260, 'Instale jpgraph'); }
        $graph = new \Graph(520, 260);
        $graph->SetScale('textlin');
        $graph->img->SetMargin(80,20,20,60);
        $graph->title->Set('Top equipos por km');
        $graph->xaxis->SetTickLabels($labels);
        $graph->xaxis->SetLabelAngle(20);
        $bar = new \BarPlot($values);
        $bar->SetFillColor('#3b82f6');
        $graph->Add($bar);
        ob_start(); $graph->Stroke(); $png = ob_get_clean();
        return response($png, 200)->header('Content-Type', 'image/png');
    }

    public function topDriversDepartures(Request $request): Response
    {
        $ok = $this->ensureJpGraph();
        $days = (int) $request->query('days', 30);
        $vehicleId = $request->filled('vehicle_id') ? (int) $request->query('vehicle_id') : null;
        $q = Movement::select('driver_id', DB::raw('COUNT(*) as total'))
            ->where('departed_at', '>=', now()->subDays(($days>0?$days:30) - 1)->startOfDay());
        if ($vehicleId) { $q->where('vehicle_id', $vehicleId); }
        $rows = $q->groupBy('driver_id')->orderByDesc('total')->limit(5)->get();
        $labels = $rows->map(function($r){ $d = Driver::find($r->driver_id); return $d?->name ?? ('#'.$r->driver_id); })->all();
        $values = $rows->pluck('total')->map(fn($x)=>(int)$x)->all();
        if (!$ok) { return $this->pngResponseFromGD(520, 260, 'Instale jpgraph'); }
        $graph = new \Graph(520, 260);
        $graph->SetScale('textlin');
        $graph->img->SetMargin(80,20,20,60);
        $graph->title->Set('Top conductores por salidas');
        $graph->xaxis->SetTickLabels($labels);
        $graph->xaxis->SetLabelAngle(20);
        $bar = new \BarPlot($values);
        $bar->SetFillColor('#10b981');
        $graph->Add($bar);
        ob_start(); $graph->Stroke(); $png = ob_get_clean();
        return response($png, 200)->header('Content-Type', 'image/png');
    }

    public function status(): \Illuminate\Http\JsonResponse
    {
        $ok = $this->ensureJpGraph();
        return response()->json(['jpgraph' => $ok]);
    }
}
