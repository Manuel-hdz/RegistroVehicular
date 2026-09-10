<?php

namespace App\Http\Controllers;

use App\Models\Part;
use App\Models\WarehouseEntry;
use App\Models\WarehouseMaterialExit;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class WarehouseMovementController extends Controller
{
    public function index(): View
    {
        $weekStart = now()->startOfWeek();
        $weekEnd = now()->endOfWeek();
        $monthStart = now()->startOfYear();

        $weeklyEntries = WarehouseEntry::whereBetween('entry_date', [$weekStart, $weekEnd])->count();
        $weeklyDepartures = WarehouseMaterialExit::whereBetween('exit_date', [$weekStart, $weekEnd])->count();
        $latestEntries = WarehouseEntry::with('materials.part')
            ->orderByDesc('entry_date')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();
        $registeredEntries = WarehouseEntry::with('materials.part')
            ->orderByDesc('entry_date')
            ->orderByDesc('created_at')
            ->get();
        $latestDepartures = WarehouseMaterialExit::with('part')
            ->orderByDesc('exit_date')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();
        $materialOptions = Part::withSum('entryMaterials as total_entries', 'quantity')
            ->withSum('materialExits as total_exits', 'quantity')
            ->orderBy('name')
            ->get(['id', 'clave', 'name', 'characteristics'])
            ->map(function (Part $part): Part {
                $part->stock_quantity = max(0, (float) ($part->total_entries ?? 0) - (float) ($part->total_exits ?? 0));
                return $part;
            })
            ->unique('clave')
            ->values();

        $departuresByMonth = WarehouseMaterialExit::where('exit_date', '>=', $monthStart)
            ->get(['exit_date'])
            ->countBy(fn (WarehouseMaterialExit $exit) => $exit->exit_date->month);

        $entriesByMonth = WarehouseEntry::where('entry_date', '>=', $monthStart)
            ->get(['entry_date'])
            ->countBy(fn (WarehouseEntry $entry) => $entry->entry_date->month);

        $monthlyLabels = [];
        $monthlyEntries = [];
        $monthlyDepartures = [];

        foreach (CarbonPeriod::create($monthStart, '1 month', now()->startOfMonth()) as $month) {
            $monthNumber = $month->month;
            $monthlyLabels[] = Carbon::create(null, $monthNumber, 1)->locale('es')->translatedFormat('M');
            $monthlyEntries[] = (int) ($entriesByMonth[$monthNumber] ?? 0);
            $monthlyDepartures[] = (int) ($departuresByMonth[$monthNumber] ?? 0);
        }

        return view('warehouse.movements', [
            'weeklyEntries' => $weeklyEntries,
            'weeklyDepartures' => $weeklyDepartures,
            'latestEntries' => $latestEntries,
            'registeredEntries' => $registeredEntries,
            'latestDepartures' => $latestDepartures,
            'materialOptions' => $materialOptions,
            'monthlyLabels' => $monthlyLabels,
            'monthlyEntries' => $monthlyEntries,
            'monthlyDepartures' => $monthlyDepartures,
        ]);
    }

    public function inventory(Request $request): View
    {
        $search = trim((string) $request->query('search', ''));

        $materials = Part::with('latestMaterialExit')
            ->withSum('entryMaterials as total_entries', 'quantity')
            ->withSum('materialExits as total_exits', 'quantity')
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('clave', 'like', '%' . $search . '%')
                        ->orWhere('name', 'like', '%' . $search . '%')
                        ->orWhere('characteristics', 'like', '%' . $search . '%');
                });
            })
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $materials->getCollection()->transform(function (Part $part): Part {
            $part->stock_quantity = max(0, (float) ($part->total_entries ?? 0) - (float) ($part->total_exits ?? 0));
            return $part;
        });

        return view('warehouse.inventory', [
            'materials' => $materials,
            'search' => $search,
        ]);
    }

    public function storeMaterial(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'entry_type' => ['nullable', 'string', 'in:Pase de salida,Compra'],
            'entry_date' => ['required', 'date'],
            'invoice_file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:10240'],
            'materials' => ['required', 'array', 'min:1'],
            'materials.*.name' => ['required', 'string', 'max:150'],
            'materials.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'materials.*.characteristics' => ['nullable', 'string', 'max:1000'],
        ]);

        $invoicePath = $request->hasFile('invoice_file')
            ? $request->file('invoice_file')->store('facturas-compras', 'public')
            : null;

        $entry = DB::transaction(function () use ($data, $invoicePath): WarehouseEntry {
            $entry = WarehouseEntry::create([
                'entry_type' => $data['entry_type'] ?? null,
                'entry_date' => $data['entry_date'],
                'invoice_path' => $invoicePath,
            ]);

            $entry->update([
                'entry_key' => 'ENT-' . $entry->entry_date->format('Ymd') . '-' . str_pad((string) $entry->id, 5, '0', STR_PAD_LEFT),
            ]);

            foreach ($data['materials'] as $materialData) {
                $characteristics = $this->normalizeCharacteristics((string) ($materialData['characteristics'] ?? ''));
                $clave = $this->generateMaterialClave($materialData['name'], $characteristics);

                $part = Part::firstOrCreate(
                    ['clave' => $clave],
                    [
                        'name' => $materialData['name'],
                        'characteristics' => $characteristics,
                        'unit_cost' => 0,
                        'active' => true,
                    ]
                );

                $entry->materials()->create([
                    'part_id' => $part->id,
                    'quantity' => $materialData['quantity'],
                ]);
            }

            return $entry;
        });

        return redirect()
            ->route('warehouse.movements')
            ->with('status', 'Entrada registrada con folio ' . $entry->entry_key . '.');
    }

    public function storeMaterialExit(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'part_id' => ['required', 'exists:parts,id'],
            'quantity' => ['nullable', 'numeric', 'min:0.01'],
            'exit_date' => ['nullable', 'date'],
            'dispatched_by' => ['nullable', 'string', 'max:180'],
            'carried_by' => ['nullable', 'string', 'max:180'],
            'destination' => ['nullable', 'string', 'max:180'],
            'responsible' => ['nullable', 'string', 'max:180'],
            'invoice_file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,webp', 'max:10240'],
        ], [
            'part_id.required' => 'Selecciona un material existente en el inventario.',
            'part_id.exists' => 'El material seleccionado no existe en el inventario.',
        ]);

        $data['quantity'] = $data['quantity'] ?? 1;
        $data['exit_date'] = $data['exit_date'] ?? now();

        $part = Part::findOrFail($data['part_id']);
        $available = max(0, (float) $part->entryMaterials()->sum('quantity') - (float) $part->materialExits()->sum('quantity'));

        if ($available <= 0 || (float) $data['quantity'] > $available) {
            return back()
                ->withErrors(['quantity' => 'El material solicitado no tiene existencia suficiente en el inventario.'])
                ->withInput();
        }

        $invoicePath = $request->hasFile('invoice_file')
            ? $request->file('invoice_file')->store('facturas-salidas', 'public')
            : null;
        unset($data['invoice_file']);

        $exit = WarehouseMaterialExit::create([
            ...$data,
            'source' => $data['dispatched_by'] ?? '',
            'destination' => $data['destination'] ?? '',
            'responsible' => $data['responsible'] ?? '',
            'invoice_path' => $invoicePath,
            'status' => 'en_curso',
        ]);
        $exit->update([
            'voucher_number' => 'VALE-' . $exit->exit_date->format('Ymd') . '-' . str_pad((string) $exit->id, 5, '0', STR_PAD_LEFT),
        ]);

        return redirect()
            ->route('warehouse.material-exits.voucher', $exit)
            ->with('status', 'Salida registrada. Vale generado.');
    }

    public function markExitDelivered(WarehouseMaterialExit $warehouseMaterialExit): RedirectResponse
    {
        if ($warehouseMaterialExit->status !== 'en_curso') {
            return redirect()
                ->route('warehouse.inventory')
                ->with('status', 'La salida ya fue marcada como entregada.');
        }

        $warehouseMaterialExit->update([
            'status' => 'entregado',
            'delivered_at' => now(),
        ]);

        return redirect()
            ->route('warehouse.inventory')
            ->with('status', 'Material marcado como entregado.');
    }

    public function voucher(WarehouseMaterialExit $warehouseMaterialExit): View
    {
        $warehouseMaterialExit->load('part');

        return view('warehouse.exit-voucher', ['exit' => $warehouseMaterialExit]);
    }

    private function normalizeCharacteristics(string $rawCharacteristics): array
    {
        return Str::of($rawCharacteristics)
            ->replace(["\r\n", "\r"], "\n")
            ->explode("\n")
            ->flatMap(fn (string $line) => explode(',', $line))
            ->map(fn (string $value) => trim($value))
            ->filter()
            ->map(fn (string $value) => (string) Str::of($value)->ascii()->lower())
            ->unique()
            ->sort()
            ->values()
            ->all();
    }

    private function generateMaterialClave(string $name, array $characteristics = []): string
    {
        $cleanName = Str::of($name)
            ->ascii()
            ->upper()
            ->replaceMatches('/[^A-Z0-9\s]/', '')
            ->trim();

        $prefix = $cleanName
            ->replaceMatches('/\s+/', '')
            ->substr(0, 6)
            ->padRight(6, 'X');

        $identity = (string) $cleanName . '|' . implode('|', $characteristics);
        $hash = 0;
        for ($index = 0; $index < strlen($identity); $index++) {
            $hash = (($hash << 5) - $hash + ord($identity[$index])) & 0xffffffff;
        }
        $suffix = strtoupper(substr(str_pad(dechex($hash), 6, '0', STR_PAD_LEFT), 0, 6));

        return 'MAT-' . $prefix . '-' . $suffix;
    }

    public function updateMaterial(Request $request, Part $part): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:150'],
        ]);

        $part->update($data);

        return redirect()
            ->route('warehouse.movements')
            ->with('status', 'Material actualizado.');
    }

    public function destroyMaterial(Part $part): RedirectResponse
    {
        $part->delete();

        return redirect()
            ->route('warehouse.movements')
            ->with('status', 'Material eliminado.');
    }
}


