<?php

namespace App\Http\Controllers;

use App\Models\Personnel;
use App\Models\PersonnelCardexEntry;
use App\Services\VacationAccrualService;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CardexController extends Controller
{
    private const CODES = [
        'A' => 'Asistencia',
        'V' => 'Vacaciones',
        'F' => 'Falta',
        'I' => 'Incapacidad',
        'PSG' => 'Permiso sin goce',
        'PCG' => 'Permiso con goce',
        'G' => 'Guardia',
        'D' => 'Descanso',
        'S' => 'Sin asignar',
    ];

    private const VIEW_MODES = ['week', 'month', 'year'];

    public function index(Request $request): View
    {
        $personnelList = Personnel::orderBy('last_name')
            ->orderBy('first_name')
            ->get();

        $selectedPersonnelId = (int) $request->input('personnel_id');
        if ($selectedPersonnelId <= 0 && $personnelList->isNotEmpty()) {
            $selectedPersonnelId = (int) $personnelList->first()->id;
        }

        $viewMode = (string) $request->input('view_mode', 'month');
        if (!in_array($viewMode, self::VIEW_MODES, true)) {
            $viewMode = 'month';
        }

        $selectedMonth = $this->resolveMonthInput((string) $request->input('month', now()->format('Y-m')));
        $selectedWeekDate = $this->resolveDateInput((string) $request->input('week_date', now()->toDateString()));
        $selectedYear = $this->resolveYearInput($request->input('year', now()->year));

        $periodConfig = $this->buildPeriodConfig($viewMode, $selectedMonth, $selectedWeekDate, $selectedYear);

        $entriesByDate = collect();
        $yearSummary = [];
        $periodSummary = array_fill_keys(array_keys(self::CODES), 0);
        $periodTotal = 0;
        $selectedPersonnel = null;
        if ($selectedPersonnelId > 0) {
            $selectedPersonnel = $personnelList->firstWhere('id', $selectedPersonnelId);
            if ($selectedPersonnel) {
                $selectedPersonnel = $this->vacationAccrualService()->sync($selectedPersonnel);

                $this->ensureDefaultEntries(
                    $selectedPersonnel->id,
                    $periodConfig['query_start'],
                    $periodConfig['query_end'],
                    optional($request->user())->id
                );

                $entries = PersonnelCardexEntry::where('personnel_id', $selectedPersonnel->id)
                    ->whereBetween('entry_date', [
                        $periodConfig['query_start']->toDateString(),
                        $periodConfig['query_end']->toDateString(),
                    ])
                    ->orderBy('entry_date')
                    ->get();

                foreach ($entries as $entry) {
                    $code = (string) $entry->code;
                    if (isset($periodSummary[$code])) {
                        $periodSummary[$code]++;
                    }
                    $periodTotal++;
                }

                if ($viewMode === 'year') {
                    $yearSummary = $this->buildYearSummary($entries, (int) $periodConfig['selected_year']);
                } else {
                    $entriesByDate = $entries->keyBy(function (PersonnelCardexEntry $entry) {
                        return $entry->entry_date->toDateString();
                    });
                }
            }
        }

        $weeks = [];
        if ($viewMode !== 'year') {
            $weeks = $this->buildWeeks(
                $periodConfig['calendar_start'],
                $periodConfig['calendar_end'],
                $periodConfig['reference_month'],
                $entriesByDate,
                $viewMode === 'month'
            );
        }

        $quickCode = strtoupper((string) $request->input('quick_code', ''));
        if (!array_key_exists($quickCode, self::CODES)) {
            $quickCode = 'A';
        }
        $quickDate = (string) $request->input('quick_date', now()->toDateString());
        try {
            $quickDate = Carbon::parse($quickDate)->toDateString();
        } catch (\Throwable $e) {
            $quickDate = now()->toDateString();
        }

        return view('hr.cardex', [
            'personnelList' => $personnelList,
            'selectedPersonnelId' => $selectedPersonnelId,
            'selectedPersonnel' => $selectedPersonnel,
            'viewMode' => $viewMode,
            'selectedMonth' => $selectedMonth->format('Y-m'),
            'selectedWeekDate' => $selectedWeekDate->toDateString(),
            'selectedYear' => (int) $selectedYear,
            'periodTitle' => $periodConfig['title'],
            'prevPeriod' => $periodConfig['prev'],
            'nextPeriod' => $periodConfig['next'],
            'yearSummary' => $yearSummary,
            'dayHeaders' => ['Jue', 'Vie', 'Sab', 'Dom', 'Lun', 'Mar', 'Mie'],
            'weeks' => $weeks,
            'codes' => self::CODES,
            'today' => now()->toDateString(),
            'quickCode' => $quickCode,
            'quickDate' => $quickDate,
            'periodSummary' => $periodSummary,
            'periodTotal' => $periodTotal,
            'pendingVacationDays' => (int) ($selectedPersonnel?->pending_vacation_days ?? 0),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'personnel_id' => ['required', 'exists:personnels,id'],
            'entry_date' => ['nullable', 'date', 'required_without:entry_date_start,entry_date_end'],
            'entry_date_start' => ['nullable', 'date', 'required_without:entry_date'],
            'entry_date_end' => ['nullable', 'date', 'required_with:entry_date_start'],
            'code' => ['required', 'in:' . implode(',', array_keys(self::CODES))],
            'notes' => ['nullable', 'string', 'max:500'],
        ], [
            'entry_date.required_without' => 'Selecciona una fecha o un rango de fechas.',
            'entry_date_start.required_without' => 'Selecciona una fecha inicial.',
            'entry_date_end.required_with' => 'Selecciona una fecha final.',
        ]);

        $singleDateInput = (string) ($data['entry_date'] ?? '');
        if ($singleDateInput !== '') {
            $rangeStart = Carbon::parse($singleDateInput)->startOfDay();
            $rangeEnd = $rangeStart->copy();
        } else {
            $rangeStart = Carbon::parse((string) $data['entry_date_start'])->startOfDay();
            $rangeEnd = Carbon::parse((string) $data['entry_date_end'])->startOfDay();
        }

        if ($rangeEnd->lt($rangeStart)) {
            throw ValidationException::withMessages([
                'entry_date_end' => 'La fecha final debe ser igual o posterior a la fecha inicial.',
            ]);
        }

        $daysInRange = (int) $rangeStart->diffInDays($rangeEnd) + 1;
        if ($daysInRange > 366) {
            throw ValidationException::withMessages([
                'entry_date_end' => 'El rango maximo permitido es de 366 dias.',
            ]);
        }

        DB::transaction(function () use ($data, $request, $rangeStart, $rangeEnd): void {
            $personnel = Personnel::whereKey((int) $data['personnel_id'])->lockForUpdate()->firstOrFail();
            $personnel = $this->vacationAccrualService()->syncLockedPersonnel($personnel);

            $newCode = $data['code'];
            $notes = $data['notes'] ?? null;
            $cursor = $rangeStart->copy();

            while ($cursor->lte($rangeEnd)) {
                $entryDate = $cursor->toDateString();
                $entry = PersonnelCardexEntry::where('personnel_id', $personnel->id)
                    ->whereDate('entry_date', $entryDate)
                    ->lockForUpdate()
                    ->first();

                $previousCode = $entry?->code;

                if ($previousCode !== 'V' && $newCode === 'V') {
                    if ((int) $personnel->pending_vacation_days <= 0) {
                        throw ValidationException::withMessages([
                            'code' => 'La persona no tiene dias de vacaciones pendientes para registrar esta clave.',
                        ]);
                    }

                    $personnel->pending_vacation_days = (int) $personnel->pending_vacation_days - 1;
                    $personnel->save();
                } elseif ($previousCode === 'V' && $newCode !== 'V') {
                    $personnel->pending_vacation_days = (int) $personnel->pending_vacation_days + 1;
                    $personnel->save();
                }

                if (!$entry) {
                    $entry = new PersonnelCardexEntry([
                        'personnel_id' => (int) $data['personnel_id'],
                        'entry_date' => $entryDate,
                    ]);
                }

                $entry->code = $newCode;
                $entry->notes = $notes;
                $entry->updated_by = optional($request->user())->id;
                $entry->save();

                $cursor->addDay();
            }
        });

        $entryDate = $rangeStart->copy();
        $viewMode = (string) $request->input('view_mode', 'month');
        if (!in_array($viewMode, self::VIEW_MODES, true)) {
            $viewMode = 'month';
        }

        $redirectParams = [
            'personnel_id' => (int) $data['personnel_id'],
            'view_mode' => $viewMode,
        ];

        if ($viewMode === 'week') {
            $weekDateInput = (string) $request->input('week_date', '');
            try {
                $redirectParams['week_date'] = $weekDateInput !== ''
                    ? Carbon::parse($weekDateInput)->toDateString()
                    : $entryDate->toDateString();
            } catch (\Throwable $e) {
                $redirectParams['week_date'] = $entryDate->toDateString();
            }
        } elseif ($viewMode === 'year') {
            $yearInput = (int) $request->input('year', 0);
            $redirectParams['year'] = $yearInput >= 2000 && $yearInput <= 2100
                ? $yearInput
                : (int) $entryDate->year;
        } else {
            $monthInput = (string) $request->input('month', '');
            try {
                $redirectParams['month'] = $monthInput !== ''
                    ? Carbon::createFromFormat('Y-m', $monthInput)->format('Y-m')
                    : $entryDate->format('Y-m');
            } catch (\Throwable $e) {
                $redirectParams['month'] = $entryDate->format('Y-m');
            }
        }

        return redirect()->route('cardex.index', $redirectParams)
            ->with('status', $daysInRange > 1 ? 'Registros de Kardex guardados.' : 'Registro de Kardex guardado.');
    }

    /**
     * @return array<int, array<int, array<string, mixed>>>
     */
    private function buildWeeks(
        Carbon $calendarStart,
        Carbon $calendarEnd,
        Carbon $monthStart,
        Collection $entriesByDate,
        bool $markOutOfMonth = true
    ): array {
        $weeks = [];
        $cursor = $calendarStart->copy();

        while ($cursor->lte($calendarEnd)) {
            $week = [];

            for ($i = 0; $i < 7; $i++) {
                $date = $cursor->copy();
                $key = $date->toDateString();
                /** @var PersonnelCardexEntry|null $entry */
                $entry = $entriesByDate->get($key);

                $week[] = [
                    'date' => $date,
                    'key' => $key,
                    'in_month' => $markOutOfMonth ? $date->month === $monthStart->month : true,
                    'entry' => $entry,
                ];

                $cursor->addDay();
            }

            $weeks[] = $week;
        }

        return $weeks;
    }

    private function resolveMonthInput(string $monthParam): Carbon
    {
        try {
            return Carbon::createFromFormat('Y-m', $monthParam)->startOfMonth();
        } catch (\Throwable $e) {
            return now()->startOfMonth();
        }
    }

    private function resolveDateInput(string $date): Carbon
    {
        try {
            return Carbon::parse($date)->startOfDay();
        } catch (\Throwable $e) {
            return now()->startOfDay();
        }
    }

    private function resolveYearInput(mixed $year): int
    {
        $yearInt = (int) $year;
        if ($yearInt < 2000 || $yearInt > 2100) {
            return (int) now()->year;
        }

        return $yearInt;
    }

    /**
     * @return array{
     *     query_start: Carbon,
     *     query_end: Carbon,
     *     calendar_start: Carbon,
     *     calendar_end: Carbon,
     *     reference_month: Carbon,
     *     title: string,
     *     prev: array<string, string|int>,
     *     next: array<string, string|int>,
     *     selected_year: int
     * }
     */
    private function buildPeriodConfig(
        string $viewMode,
        Carbon $selectedMonth,
        Carbon $selectedWeekDate,
        int $selectedYear
    ): array {
        if ($viewMode === 'week') {
            $weekStart = $this->workWeekStart($selectedWeekDate);
            $weekEnd = $weekStart->copy()->addDays(6);
            $weekLabel = sprintf(
                'Semana del %s al %s',
                $weekStart->format('d/m/Y'),
                $weekEnd->format('d/m/Y')
            );

            return [
                'query_start' => $weekStart,
                'query_end' => $weekEnd,
                'calendar_start' => $weekStart,
                'calendar_end' => $weekEnd,
                'reference_month' => $selectedWeekDate->copy()->startOfMonth(),
                'title' => $weekLabel,
                'prev' => [
                    'view_mode' => 'week',
                    'week_date' => $selectedWeekDate->copy()->subWeek()->toDateString(),
                ],
                'next' => [
                    'view_mode' => 'week',
                    'week_date' => $selectedWeekDate->copy()->addWeek()->toDateString(),
                ],
                'selected_year' => (int) $selectedWeekDate->year,
            ];
        }

        if ($viewMode === 'year') {
            $yearStart = Carbon::create($selectedYear, 1, 1)->startOfDay();
            $yearEnd = Carbon::create($selectedYear, 12, 31)->endOfDay();

            return [
                'query_start' => $yearStart,
                'query_end' => $yearEnd,
                'calendar_start' => $yearStart,
                'calendar_end' => $yearEnd,
                'reference_month' => $yearStart,
                'title' => "Anio {$selectedYear}",
                'prev' => [
                    'view_mode' => 'year',
                    'year' => $selectedYear - 1,
                ],
                'next' => [
                    'view_mode' => 'year',
                    'year' => $selectedYear + 1,
                ],
                'selected_year' => $selectedYear,
            ];
        }

        $monthStart = $selectedMonth->copy()->startOfMonth();
        $monthEnd = $selectedMonth->copy()->endOfMonth();

        $calendarStart = $this->workWeekStart($monthStart);
        $calendarEnd = $monthEnd->copy();
        while ($calendarEnd->dayOfWeek !== Carbon::WEDNESDAY) {
            $calendarEnd->addDay();
        }

        return [
            'query_start' => $calendarStart,
            'query_end' => $calendarEnd,
            'calendar_start' => $calendarStart,
            'calendar_end' => $calendarEnd,
            'reference_month' => $monthStart,
            'title' => ucfirst($selectedMonth->copy()->locale('es')->translatedFormat('F Y')),
            'prev' => [
                'view_mode' => 'month',
                'month' => $selectedMonth->copy()->subMonth()->format('Y-m'),
            ],
            'next' => [
                'view_mode' => 'month',
                'month' => $selectedMonth->copy()->addMonth()->format('Y-m'),
            ],
            'selected_year' => (int) $selectedMonth->year,
        ];
    }

    private function workWeekStart(Carbon $date): Carbon
    {
        $weekStart = $date->copy()->startOfDay();
        while ($weekStart->dayOfWeek !== Carbon::THURSDAY) {
            $weekStart->subDay();
        }

        return $weekStart;
    }

    /**
     * @param Collection<int, PersonnelCardexEntry> $entries
     * @return array<int, array{month: string, counts: array<string, int>, total: int}>
     */
    private function buildYearSummary(Collection $entries, int $year): array
    {
        $summary = [];
        $codeKeys = array_keys(self::CODES);

        for ($month = 1; $month <= 12; $month++) {
            $summary[$month] = [
                'month' => ucfirst(Carbon::create($year, $month, 1)->locale('es')->translatedFormat('F')),
                'counts' => array_fill_keys($codeKeys, 0),
                'total' => 0,
            ];
        }

        foreach ($entries as $entry) {
            $month = (int) $entry->entry_date->month;
            if (!isset($summary[$month])) {
                continue;
            }

            $code = (string) $entry->code;
            if (isset($summary[$month]['counts'][$code])) {
                $summary[$month]['counts'][$code]++;
            }
            $summary[$month]['total']++;
        }

        return array_values($summary);
    }

    private function ensureDefaultEntries(
        int $personnelId,
        Carbon $queryStart,
        Carbon $queryEnd,
        ?int $userId
    ): void {
        $today = now()->startOfDay();
        $start = $queryStart->copy()->startOfDay();
        $end = $queryEnd->copy()->startOfDay();

        if ($end->gt($today)) {
            $end = $today;
        }
        if ($start->gt($end)) {
            return;
        }

        $existingDates = PersonnelCardexEntry::where('personnel_id', $personnelId)
            ->whereBetween('entry_date', [$start->toDateString(), $end->toDateString()])
            ->pluck('entry_date')
            ->map(static fn ($date): string => Carbon::parse($date)->toDateString())
            ->all();
        $existingLookup = array_flip($existingDates);

        $now = now();
        $rows = [];
        $cursor = $start->copy();
        while ($cursor->lte($end)) {
            $dateKey = $cursor->toDateString();
            if (!isset($existingLookup[$dateKey])) {
                $rows[] = [
                    'personnel_id' => $personnelId,
                    'entry_date' => $dateKey,
                    'code' => 'S',
                    'notes' => 'Sin asignar (auto)',
                    'updated_by' => $userId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }
            $cursor->addDay();
        }

        if (count($rows) > 0) {
            DB::table('personnel_cardex_entries')->insertOrIgnore($rows);
        }
    }

    private function vacationAccrualService(): VacationAccrualService
    {
        return app(VacationAccrualService::class);
    }
}
