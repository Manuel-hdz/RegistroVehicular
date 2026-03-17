<?php

namespace App\Http\Controllers;

use App\Models\ComedorRecord;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ComedorController extends Controller
{
    public function index(): View
    {
        return view('comedor.index', [
            'activeTab' => 'registro',
            'records' => null,
            'todayCount' => null,
            'totalCount' => null,
            'lastRecord' => $this->latestRecord(),
            'currentTime' => now($this->timezone()),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
        ]);

        $record = ComedorRecord::create([
            'name' => trim($data['name']),
            'recorded_at' => now($this->timezone())->utc(),
        ]);

        return redirect()
            ->route('comedor.index')
            ->with('status', 'Registro guardado para '.$record->name.' a las '.$record->recorded_at->format('H:i:s').' del '.$record->recorded_at->format('d/m/Y').'.');
    }

    public function records(): View
    {
        $todayStart = now($this->timezone())->startOfDay()->utc();
        $todayEnd = now($this->timezone())->endOfDay()->utc();
        $records = ComedorRecord::query()
            ->latest('recorded_at')
            ->simplePaginate(20)
            ->withQueryString();

        return view('comedor.index', [
            'activeTab' => 'registros',
            'records' => $records,
            'todayCount' => ComedorRecord::query()->whereBetween('recorded_at', [$todayStart, $todayEnd])->count(),
            'totalCount' => ComedorRecord::query()->count(),
            'lastRecord' => $records->getCollection()->first() ?? $this->latestRecord(),
            'currentTime' => now($this->timezone()),
        ]);
    }

    protected function latestRecord(): ?ComedorRecord
    {
        return ComedorRecord::query()->latest('recorded_at')->first();
    }

    protected function timezone(): string
    {
        return 'America/Mexico_City';
    }
}
