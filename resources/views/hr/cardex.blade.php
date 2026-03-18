@extends('layouts.app')

@section('content')
@push('head')
<style>
    .cardex-top-grid {
        display: grid;
        grid-template-columns: 1.2fr 1fr;
        gap: 10px;
        align-items: start;
    }
    .cardex-panel {
        border: 1px solid #d8dee6;
        border-radius: 14px;
        background: linear-gradient(180deg, #ffffff, #fbfcfd);
        padding: 10px;
        box-shadow: 0 1px 2px rgba(15, 23, 42, .04);
    }
    .cardex-panel h3 {
        margin: 0 0 8px;
        font-size: 16px;
        color: #111827;
    }
    .cardex-panel .grid {
        gap: 9px;
    }
    .cardex-panel label {
        font-size: 13px;
        margin-bottom: 4px;
        color: #4b5563;
    }
    .cardex-panel input,
    .cardex-panel select {
        border: 1px solid #cfd8e3;
        border-radius: 10px;
        padding: 8px 10px;
        font-size: 15px;
        background: #fff;
    }
    .cardex-panel .btn {
        min-height: 38px;
        padding: 8px 12px;
        font-size: 14px;
    }
    .cardex-query-grid {
        display: grid;
        gap: 9px;
    }
    .cardex-query-pair {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
    }
    .cardex-action-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
    }
    .cardex-action-row .btn {
        width: 100%;
        justify-content: center;
    }
    .cardex-capture-pair {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 8px;
    }
    .cardex-save-row {
        display: flex;
        justify-content: flex-end;
    }
    .cardex-save-row .btn {
        min-height: 38px;
        padding: 8px 14px;
    }
    .cardex-context {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        gap: 12px;
        flex-wrap: wrap;
        border: 1px solid #dbe3ea;
        border-radius: 12px;
        background: linear-gradient(180deg, #f9fbfd, #f3f7fb);
        padding: 12px 14px;
    }
    .cardex-context-label {
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: .04em;
        color: #6b7280;
        margin-bottom: 2px;
    }
    .cardex-context-value {
        font-size: 16px;
        font-weight: 700;
        color: #1f2937;
    }
    .cardex-toolbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 10px;
        flex-wrap: wrap;
    }
    .cardex-legend {
        display: flex;
        gap: 8px;
        flex-wrap: wrap;
        margin-top: 12px;
    }
    .cardex-badge {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        border-radius: 999px;
        padding: 4px 10px;
        font-size: 12px;
        font-weight: 700;
        border: 1px solid transparent;
    }
    .cardex-calendar th,
    .cardex-calendar td {
        text-align: left;
        vertical-align: top;
    }
    .cardex-calendar {
        table-layout: fixed;
    }
    .cardex-calendar th {
        width: calc(100% / 7);
        padding: 6px 5px;
        font-size: 13px;
    }
    .cardex-calendar td {
        min-width: 0;
        width: calc(100% / 7);
        height: 78px;
        border: 1px solid #dfe3e8;
        background: #fff;
        padding: 5px;
        position: relative;
    }
    .cardex-calendar td.has-note {
        cursor: help;
    }
    .cardex-calendar td.has-note::after {
        content: attr(data-note);
        position: absolute;
        left: 8px;
        right: 8px;
        bottom: calc(100% + 8px);
        background: #111827;
        color: #fff;
        border-radius: 10px;
        padding: 8px 10px;
        font-size: 12px;
        line-height: 1.3;
        box-shadow: 0 10px 18px rgba(0, 0, 0, .25);
        opacity: 0;
        visibility: hidden;
        pointer-events: none;
        transform: translateY(4px);
        transition: opacity .15s ease, transform .15s ease;
        z-index: 20;
        white-space: normal;
    }
    .cardex-calendar td.has-note:hover::after {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
    }
    .cardex-calendar td.editable-entry {
        cursor: pointer;
        transition: box-shadow .15s ease, transform .15s ease;
    }
    .cardex-calendar td.editable-entry:hover {
        box-shadow: inset 0 0 0 2px #94a3b8;
        transform: translateY(-1px);
    }
    .cardex-calendar .out-month {
        background: #f8fafc;
        color: #94a3b8;
    }
    .cardex-day-num {
        font-weight: 700;
        font-size: 12px;
    }
    .cardex-code {
        display: inline-block;
        margin-top: 3px;
        font-size: 11px;
        font-weight: 800;
        border-radius: 999px;
        padding: 1px 6px;
        border: 1px solid transparent;
    }
    .cardex-text {
        margin-top: 2px;
        font-size: 10px;
        line-height: 1.2;
    }
    .status-A { background: #e8f8ef !important; }
    .status-A .cardex-code { background: #166534; color: #fff; border-color: #14532d; }
    .status-V { background: #ecfdf3 !important; }
    .status-V .cardex-code { background: #16a34a; color: #fff; border-color: #15803d; }
    .status-F { background: #feeff0 !important; }
    .status-F .cardex-code { background: #b91c1c; color: #fff; border-color: #7f1d1d; }
    .status-I { background: #fff4e5 !important; }
    .status-I .cardex-code { background: #b45309; color: #fff; border-color: #92400e; }
    .status-PSG { background: #f2f4f7 !important; }
    .status-PSG .cardex-code { background: #475467; color: #fff; border-color: #344054; }
    .status-PCG { background: #ebf8ff !important; }
    .status-PCG .cardex-code { background: #1d4ed8; color: #fff; border-color: #1e40af; }
    .status-G { background: #f4f0ff !important; }
    .status-G .cardex-code { background: #6d28d9; color: #fff; border-color: #5b21b6; }
    .status-D { background: #ecfeff !important; color: #0f172a; }
    .status-D .cardex-code { background: #0e7490; color: #fff; border-color: #155e75; }
    .status-S { background: #ffffff !important; color: #111827; }
    .status-S .cardex-code { background: #ffffff; color: #111827; border-color: #9ca3af; }
    .legend-A { background: #e8f8ef; border-color: #166534; color: #14532d; }
    .legend-V { background: #ecfdf3; border-color: #16a34a; color: #166534; }
    .legend-F { background: #feeff0; border-color: #b91c1c; color: #7f1d1d; }
    .legend-I { background: #fff4e5; border-color: #b45309; color: #92400e; }
    .legend-PSG { background: #f2f4f7; border-color: #475467; color: #344054; }
    .legend-PCG { background: #ebf8ff; border-color: #1d4ed8; color: #1e40af; }
    .legend-G { background: #f4f0ff; border-color: #6d28d9; color: #5b21b6; }
    .legend-D { background: #ecfeff; border-color: #0e7490; color: #155e75; }
    .legend-S { background: #ffffff; border-color: #9ca3af; color: #111827; }
    .cardex-year-table th,
    .cardex-year-table td {
        font-size: 14px;
        white-space: nowrap;
    }
    .cardex-nav-bottom {
        display: flex;
        justify-content: center;
        margin-top: 14px;
    }
    .cardex-nav-group {
        display: inline-flex;
        gap: 8px;
        align-items: center;
        border: 1px solid #d1d5db;
        border-radius: 999px;
        padding: 5px;
        background: #f8fafc;
    }
    .cardex-nav-btn {
        border-radius: 999px;
        border: 1px solid transparent;
        background: #ffffff;
        color: #1f2937;
        font-weight: 600;
        font-size: 14px;
        text-decoration: none;
        padding: 7px 14px;
        display: inline-flex;
        align-items: center;
        gap: 6px;
        transition: all .18s ease;
    }
    .cardex-nav-btn:hover {
        background: #eef2f7;
        color: #111827;
        border-color: #cbd5e1;
    }
    .cardex-nav-btn.next {
        background: #e8f8ef;
        border-color: #b7e4c7;
        color: #14532d;
    }
    .cardex-nav-btn.next:hover {
        background: #d9f3e4;
        border-color: #93d1af;
        color: #14532d;
    }
    .cardex-summary {
        border: 1px solid #d1d5db;
        border-radius: 12px;
        background: #fbfcfe;
        padding: 10px 12px;
    }
    .cardex-results-grid {
        margin-top: 12px;
        display: grid;
        grid-template-columns: minmax(0, 1fr) 280px;
        gap: 12px;
        align-items: start;
    }
    .cardex-results-table {
        min-width: 0;
    }
    .cardex-summary-title {
        margin: 0 0 8px;
        font-size: 14px;
        font-weight: 700;
        color: #111827;
    }
    .cardex-summary-grid {
        display: grid;
        grid-template-columns: 1fr;
        gap: 8px;
    }
    .cardex-summary-item {
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        background: #fff;
        padding: 6px 8px;
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        position: relative;
    }
    .cardex-summary-code {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 28px;
        height: 28px;
        padding: 0 8px;
        border-radius: 999px;
        border: 1px solid #cfd8e3;
        background: #f8fafc;
        color: #111827;
        font-size: 13px;
        font-weight: 800;
    }
    .cardex-summary-item.has-hint {
        cursor: help;
    }
    .cardex-summary-item.has-hint::after {
        content: attr(data-hint);
        position: absolute;
        left: 8px;
        right: 8px;
        bottom: calc(100% + 8px);
        background: #111827;
        color: #fff;
        border-radius: 10px;
        padding: 7px 9px;
        font-size: 12px;
        line-height: 1.3;
        box-shadow: 0 10px 18px rgba(0, 0, 0, .25);
        opacity: 0;
        visibility: hidden;
        pointer-events: none;
        transform: translateY(4px);
        transition: opacity .15s ease, transform .15s ease;
        z-index: 20;
    }
    .cardex-summary-item.has-hint:hover::after {
        opacity: 1;
        visibility: visible;
        transform: translateY(0);
    }
    .cardex-summary-item small {
        display: none;
        color: #6b7280;
        font-size: 11px;
        line-height: 1.2;
    }
    .cardex-summary-item strong {
        display: inline-block;
        font-size: 16px;
        color: #111827;
        line-height: 1.1;
        margin-top: 0;
    }
    .cardex-vacation-pill {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 54px;
        height: 54px;
        border-radius: 999px;
        background: linear-gradient(180deg, #34d399, #16a34a);
        color: #fff;
        font-size: 22px;
        font-weight: 800;
        box-shadow: 0 10px 22px rgba(22, 163, 74, .24);
    }
    .cardex-print-action {
        margin-top: 12px;
        display: flex;
        justify-content: flex-end;
    }
    .cardex-modal-backdrop {
        position: fixed;
        inset: 0;
        background: rgba(2, 6, 23, .45);
        display: none;
        align-items: center;
        justify-content: center;
        z-index: 2100;
        padding: 14px;
    }
    .cardex-modal-backdrop[aria-hidden="false"] {
        display: flex;
    }
    .cardex-modal {
        width: min(560px, 96vw);
        background: #fff;
        border: 1px solid #d1d5db;
        border-radius: 14px;
        box-shadow: 0 18px 40px rgba(0, 0, 0, .2);
        overflow: hidden;
    }
    .cardex-modal-head {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        padding: 10px 12px;
        border-bottom: 1px solid #e5e7eb;
        background: #f8fafc;
    }
    .cardex-modal-head h4 {
        margin: 0;
        font-size: 16px;
    }
    .cardex-modal-close {
        border: 0;
        background: transparent;
        font-size: 20px;
        line-height: 1;
        color: #6b7280;
        cursor: pointer;
    }
    .cardex-modal-body {
        padding: 12px;
    }
    .cardex-modal-actions {
        padding: 0 12px 12px;
        display: flex;
        justify-content: flex-end;
        gap: 8px;
    }
    @media print {
        body * {
            visibility: hidden !important;
        }
        .cardex-print-area,
        .cardex-print-area * {
            visibility: visible !important;
        }
        .cardex-print-area {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            max-width: 100%;
            padding: 0;
            margin: 0;
        }
        .cardex-print-area .cardex-results-grid {
            display: block !important;
        }
        .cardex-print-area .table-responsive {
            overflow: visible !important;
        }
        .cardex-print-area .cardex-calendar td {
            min-width: 0 !important;
            width: calc(100% / 7) !important;
            height: 68px !important;
            padding: 2px !important;
        }
        .cardex-print-area .cardex-day-num {
            font-size: 11px !important;
        }
        .cardex-print-area .cardex-code {
            font-size: 10px !important;
            margin-top: 2px !important;
            padding: 1px 6px !important;
        }
        .cardex-print-area .cardex-text {
            font-size: 10px !important;
            margin-top: 2px !important;
        }
        .cardex-print-area .cardex-summary {
            margin-top: 8px !important;
            border-color: #000 !important;
            background: #fff !important;
        }
        .cardex-print-area .cardex-summary-grid {
            grid-template-columns: repeat(4, minmax(0, 1fr)) !important;
            gap: 4px !important;
        }
        .cardex-print-area .cardex-summary-item {
            border-color: #000 !important;
            padding: 4px 5px !important;
        }
        .cardex-print-area .cardex-summary-item strong {
            font-size: 12px !important;
        }
        .no-print {
            display: none !important;
        }
    }
    @media (max-width: 992px) {
        .cardex-top-grid {
            grid-template-columns: 1fr;
        }
        .cardex-query-pair,
        .cardex-action-row,
        .cardex-capture-pair {
            grid-template-columns: 1fr;
        }
        .cardex-results-grid {
            grid-template-columns: 1fr;
        }
        .cardex-context-value {
            font-size: 15px;
        }
        .cardex-nav-group {
            width: 100%;
            justify-content: space-between;
        }
        .cardex-nav-btn {
            flex: 1;
            justify-content: center;
        }
        .cardex-summary-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endpush

<div class="card no-print">
    <h2 style="margin:0;">Kardex de asistencias</h2>
    <p style="margin:8px 0 0; color:#4b5563;">
        Selecciona personal y periodo a la izquierda. Captura claves a la derecha.
    </p>
</div>

<div class="card no-print">
    <div class="cardex-top-grid">
        <div class="cardex-panel">
            <h3>Consulta</h3>
            <form method="GET" action="{{ route('cardex.index') }}" class="cardex-query-grid">
                <div>
                    <label>Personal</label>
                    <select id="cardexPersonnelSelect" class="searchable-select" name="personnel_id" required {{ $personnelList->isEmpty() ? 'disabled' : '' }}>
                        @forelse($personnelList as $person)
                            <option value="{{ $person->id }}" {{ (int) $selectedPersonnelId === (int) $person->id ? 'selected' : '' }}>
                                {{ $person->employee_number }} - {{ $person->full_name }}
                            </option>
                        @empty
                            <option value="">Sin personal registrado</option>
                        @endforelse
                    </select>
                </div>
                <div class="cardex-query-pair">
                    <div>
                        <label>Vista</label>
                        <select name="view_mode" onchange="this.form.submit()">
                            <option value="week" {{ $viewMode === 'week' ? 'selected' : '' }}>Semana</option>
                            <option value="month" {{ $viewMode === 'month' ? 'selected' : '' }}>Mes</option>
                            <option value="year" {{ $viewMode === 'year' ? 'selected' : '' }}>Anio</option>
                        </select>
                    </div>
                    <div>
                        @if($viewMode === 'week')
                            <label>Fecha base</label>
                            <input type="date" name="week_date" value="{{ $selectedWeekDate }}">
                        @elseif($viewMode === 'year')
                            <label>Anio base</label>
                            <input type="number" name="year" min="2000" max="2100" value="{{ $selectedYear }}">
                        @else
                            <label>Mes base</label>
                            <input type="month" name="month" value="{{ $selectedMonth }}">
                        @endif
                    </div>
                </div>
                <div class="cardex-action-row">
                    <button class="btn btn-primary" type="submit">Aplicar</button>
                    <a class="btn btn-secondary" href="{{ route('cardex.index') }}">Limpiar</a>
                </div>
            </form>
        </div>

        <div class="cardex-panel">
            <h3>Captura de clave</h3>
            @if(!$selectedPersonnel)
                <p style="margin:8px 0 0; color:#6b7280;">
                    Selecciona personal en el panel izquierdo para habilitar la captura.
                </p>
            @else
                <form method="POST" action="{{ route('cardex.store') }}" class="grid">
                    @csrf
                    <input type="hidden" name="personnel_id" value="{{ $selectedPersonnel->id }}">
                    <input type="hidden" name="view_mode" value="{{ $viewMode }}">
                    <input type="hidden" name="week_date" value="{{ $selectedWeekDate }}">
                    <input type="hidden" name="month" value="{{ $selectedMonth }}">
                    <input type="hidden" name="year" value="{{ $selectedYear }}">
                    <div class="cardex-capture-pair">
                        <div>
                            <label>Fecha</label>
                            <input type="date" name="entry_date" value="{{ old('entry_date', $quickDate ?? $today) }}" required>
                        </div>
                        <div>
                            <label>Clave</label>
                            <select name="code" required>
                                @foreach($codes as $code => $label)
                                    <option value="{{ $code }}" {{ old('code', $quickCode ?? 'A') === $code ? 'selected' : '' }}>
                                        {{ $code }} - {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div>
                        <div style="margin:0 0 6px; font-size:13px; color:#166534; font-weight:700;">
                            Vacaciones pendientes disponibles: {{ $pendingVacationDays }}
                        </div>
                        <label>Notas (opcional)</label>
                        <input name="notes" value="{{ old('notes') }}">
                    </div>
                    <div class="cardex-save-row">
                        <button class="btn btn-primary" type="submit">Guardar clave</button>
                    </div>
                </form>
            @endif
        </div>
    </div>
</div>

@if(!$selectedPersonnel)
    <div class="error">No hay personal registrado para mostrar el kardex.</div>
@else
    <div class="card">
        <div class="cardex-print-area" id="cardexPrintArea">
            <div class="cardex-context">
                <div>
                    <div class="cardex-context-label">Personal</div>
                    <div class="cardex-context-value">{{ $selectedPersonnel->employee_number }} - {{ $selectedPersonnel->full_name }}</div>
                </div>
                <div>
                    <div class="cardex-context-label">Periodo consultado</div>
                    <div class="cardex-context-value">{{ $periodTitle }}</div>
                </div>
                <div>
                    <div class="cardex-context-label">Vacaciones pendientes</div>
                    <div class="cardex-context-value">
                        <span class="cardex-vacation-pill">{{ $pendingVacationDays }}</span>
                    </div>
                </div>
            </div>

            <div class="cardex-legend">
                @foreach($codes as $code => $label)
                    <span class="cardex-badge legend-{{ $code }}">{{ $code }} - {{ $label }}</span>
                @endforeach
            </div>
            @if($viewMode !== 'year')
                <p class="no-print" style="margin:8px 0 0; color:#475569; font-size:13px;">
                    Tip: da clic en cualquier dia con clave para editarla.
                </p>
            @endif

            <div class="cardex-results-grid">
                <div class="cardex-results-table">
                    @if($viewMode === 'year')
                        <div class="table-responsive">
                            <table class="table cardex-year-table align-middle" style="width:100%;">
                                <thead>
                                    <tr>
                                        <th>Mes</th>
                                        @foreach($codes as $code => $label)
                                            <th>{{ $code }}</th>
                                        @endforeach
                                        <th>Total</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $totalsByCode = array_fill_keys(array_keys($codes), 0);
                                        $yearTotal = 0;
                                    @endphp
                                    @foreach($yearSummary as $row)
                                        <tr>
                                            <td><strong>{{ $row['month'] }}</strong></td>
                                            @foreach($codes as $code => $label)
                                                @php
                                                    $value = (int) ($row['counts'][$code] ?? 0);
                                                @endphp
                                                <td>{{ $value }}</td>
                                                @php
                                                    $totalsByCode[$code] += $value;
                                                @endphp
                                            @endforeach
                                            <td><strong>{{ $row['total'] }}</strong></td>
                                            @php
                                                $yearTotal += (int) $row['total'];
                                            @endphp
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <th>Total anual</th>
                                        @foreach($codes as $code => $label)
                                            <th>{{ $totalsByCode[$code] }}</th>
                                        @endforeach
                                        <th>{{ $yearTotal }}</th>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table cardex-calendar align-middle" style="width:100%;">
                                <thead>
                                    <tr>
                                        @foreach($dayHeaders as $day)
                                            <th>{{ $day }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($weeks as $week)
                                        <tr>
                                            @foreach($week as $day)
                                                @php
                                                    $entry = $day['entry'];
                                                    $code = $entry?->code;
                                                    $note = trim((string) ($entry?->notes ?? ''));
                                                    $dayClasses = [];
                                                    if (!$day['in_month']) {
                                                        $dayClasses[] = 'out-month';
                                                    }
                                                    if ($code) {
                                                        $dayClasses[] = 'status-' . $code;
                                                    }
                                                    if ($note !== '') {
                                                        $dayClasses[] = 'has-note';
                                                    }
                                                    if ($entry) {
                                                        $dayClasses[] = 'editable-entry';
                                                    }
                                                @endphp
                                                <td
                                                    class="{{ implode(' ', $dayClasses) }}"
                                                    @if($note !== '')
                                                        title="Nota: {{ $note }}"
                                                        data-note="Nota: {{ $note }}"
                                                    @endif
                                                    @if($entry)
                                                        data-editable="1"
                                                        data-entry-date="{{ $day['key'] }}"
                                                        data-entry-code="{{ $entry->code }}"
                                                        data-entry-note="{{ $note }}"
                                                        aria-label="Editar clave del dia {{ $day['date']->format('d/m/Y') }}"
                                                    @endif
                                                >
                                                    <div class="cardex-day-num">{{ $day['date']->format('d') }}</div>
                                                    @if($entry)
                                                        <span class="cardex-code">{{ $entry->code }}</span>
                                                        <div class="cardex-text">{{ $codes[$entry->code] ?? '' }}</div>
                                                    @endif
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>

                <div class="cardex-summary">
                    <h4 class="cardex-summary-title">Ausentismos</h4>
                    <div class="cardex-summary-grid">
                        @php
                            $ausentismoTotal = 0;
                        @endphp
                        @foreach($codes as $code => $label)
                            @if(!in_array($code, ['A', 'S', 'D', 'G'], true))
                                @php
                                    $count = (int) ($periodSummary[$code] ?? 0);
                                    $ausentismoTotal += $count;
                                @endphp
                                <div class="cardex-summary-item has-hint" title="{{ $label }}" data-hint="{{ $code }}: {{ $label }}">
                                    <span class="cardex-summary-code">{{ $code }}</span>
                                    <strong>{{ $count }}</strong>
                                </div>
                            @endif
                        @endforeach
                        <div class="cardex-summary-item">
                            <span class="cardex-summary-code" style="min-width:58px;">Total</span>
                            <strong>{{ $ausentismoTotal }}</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="cardex-print-action no-print">
            <button type="button" class="btn btn-secondary" id="btnPrintCardex">
                <i class="bi bi-printer"></i> Imprimir
            </button>
        </div>

        <div class="cardex-nav-bottom no-print">
            <div class="cardex-nav-group">
                <a class="cardex-nav-btn prev" href="{{ route('cardex.index', array_merge(['personnel_id' => $selectedPersonnel->id], $prevPeriod)) }}">
                    <i class="bi bi-chevron-left"></i>
                    @if($viewMode === 'week')
                        Semana anterior
                    @elseif($viewMode === 'year')
                        Anio anterior
                    @else
                        Mes anterior
                    @endif
                </a>
                <a class="cardex-nav-btn next" href="{{ route('cardex.index', array_merge(['personnel_id' => $selectedPersonnel->id], $nextPeriod)) }}">
                    @if($viewMode === 'week')
                        Semana siguiente
                    @elseif($viewMode === 'year')
                        Anio siguiente
                    @else
                        Mes siguiente
                    @endif
                    <i class="bi bi-chevron-right"></i>
                </a>
            </div>
        </div>
    </div>

    @if($viewMode !== 'year')
        <div class="cardex-modal-backdrop no-print" id="editEntryModal" aria-hidden="true">
            <div class="cardex-modal" role="dialog" aria-modal="true" aria-labelledby="editEntryTitle">
                <div class="cardex-modal-head">
                    <h4 id="editEntryTitle">Editar clave del dia</h4>
                    <button type="button" class="cardex-modal-close" id="btnCloseEditEntry" aria-label="Cerrar">x</button>
                </div>
                <form method="POST" action="{{ route('cardex.store') }}" id="editEntryForm">
                    @csrf
                    <div class="cardex-modal-body grid">
                        <input type="hidden" name="personnel_id" value="{{ $selectedPersonnel->id }}">
                        <input type="hidden" name="view_mode" value="{{ $viewMode }}">
                        <input type="hidden" name="week_date" value="{{ $selectedWeekDate }}">
                        <input type="hidden" name="month" value="{{ $selectedMonth }}">
                        <input type="hidden" name="year" value="{{ $selectedYear }}">
                        <div class="cardex-capture-pair">
                            <div>
                                <label>Fecha</label>
                                <input type="date" name="entry_date" id="editEntryDate" readonly>
                            </div>
                            <div>
                                <label>Clave</label>
                                <select name="code" id="editEntryCode" required>
                                    @foreach($codes as $code => $label)
                                        <option value="{{ $code }}">{{ $code }} - {{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div>
                            <label>Motivo / nota</label>
                            <textarea name="notes" id="editEntryNotes" rows="3" placeholder="Escribe el motivo del cambio"></textarea>
                        </div>
                    </div>
                    <div class="cardex-modal-actions">
                        <button type="button" class="btn btn-secondary" id="btnCancelEditEntry">Cancelar</button>
                        <button type="submit" class="btn btn-primary">Guardar cambios</button>
                    </div>
                </form>
            </div>
        </div>
    @endif
@endif
@endsection

@push('scripts')
<script>
    (function () {
        function makeSearchable(select) {
            if (!select) return;

            const wrapper = document.createElement('div');
            wrapper.style.position = 'relative';
            wrapper.className = 'searchable-wrapper';

            const input = document.createElement('input');
            input.type = 'text';
            input.placeholder = 'Buscar...';
            input.className = 'searchable-input';
            input.autocomplete = 'off';

            const list = document.createElement('ul');
            list.className = 'searchable-list';
            list.style.position = 'absolute';
            list.style.left = '0';
            list.style.right = '0';
            list.style.top = '100%';
            list.style.zIndex = '10';
            list.style.maxHeight = '160px';
            list.style.overflowY = 'auto';
            list.style.margin = '4px 0 0';
            list.style.padding = '0';
            list.style.listStyle = 'none';
            list.style.background = '#fff';
            list.style.border = '1px solid #ccc';
            list.style.boxShadow = '0 2px 6px rgba(0,0,0,.1)';
            list.hidden = true;

            const originalOptions = Array.from(select.options);

            function applyOption(opt) {
                if (!opt) return;
                select.value = opt.value;
                input.value = opt.textContent;
                list.hidden = true;
                select.dispatchEvent(new Event('change', { bubbles: true }));
            }

            function render(filter) {
                list.innerHTML = '';
                const term = (filter || '').toLowerCase();
                originalOptions.forEach(function (opt) {
                    if (!opt.value) return;
                    const text = opt.textContent;
                    if (term && !text.toLowerCase().includes(term)) return;
                    const li = document.createElement('li');
                    li.textContent = text;
                    li.dataset.value = opt.value;
                    li.style.padding = '6px 8px';
                    li.style.cursor = 'pointer';
                    li.addEventListener('mousedown', function (event) {
                        event.preventDefault();
                        applyOption(opt);
                    });
                    list.appendChild(li);
                });
                list.hidden = list.children.length === 0;
            }

            input.addEventListener('focus', function () {
                input.select();
                render(input.value);
            });

            input.addEventListener('input', function () {
                render(this.value);
            });

            input.addEventListener('keydown', function (event) {
                if (event.key !== 'Enter') return;
                event.preventDefault();
                const firstVisible = list.querySelector('li');
                if (firstVisible) {
                    const match = originalOptions.find(function (opt) {
                        return opt.value === firstVisible.dataset.value;
                    });
                    applyOption(match);
                }
            });

            document.addEventListener('click', function (event) {
                if (!wrapper.contains(event.target)) {
                    list.hidden = true;
                }
            });

            select.parentNode.insertBefore(wrapper, select);
            wrapper.appendChild(input);
            wrapper.appendChild(list);
            select.style.display = 'none';

            const selectedOpt = select.selectedOptions[0];
            if (selectedOpt) {
                input.value = selectedOpt.textContent;
            }
        }

        document.querySelectorAll('.searchable-select').forEach(makeSearchable);

        const printBtn = document.getElementById('btnPrintCardex');
        if (printBtn) {
            printBtn.addEventListener('click', function () {
                window.print();
            });
        }

        const modal = document.getElementById('editEntryModal');
        const closeBtn = document.getElementById('btnCloseEditEntry');
        const cancelBtn = document.getElementById('btnCancelEditEntry');
        const form = document.getElementById('editEntryForm');
        if (!modal || !form) {
            return;
        }

        const inputDate = document.getElementById('editEntryDate');
        const inputCode = document.getElementById('editEntryCode');
        const inputNotes = document.getElementById('editEntryNotes');

        function openModal() {
            modal.setAttribute('aria-hidden', 'false');
        }

        function closeModal() {
            modal.setAttribute('aria-hidden', 'true');
        }

        document.querySelectorAll('td[data-editable="1"]').forEach(function (cell) {
            cell.addEventListener('click', function () {
                const date = cell.getAttribute('data-entry-date') || '';
                const code = cell.getAttribute('data-entry-code') || 'A';
                const note = cell.getAttribute('data-entry-note') || '';
                inputDate.value = date;
                inputCode.value = code;
                inputNotes.value = note;
                openModal();
            });
        });

        closeBtn?.addEventListener('click', closeModal);
        cancelBtn?.addEventListener('click', closeModal);
        modal.addEventListener('click', function (event) {
            if (event.target === modal) {
                closeModal();
            }
        });
        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && modal.getAttribute('aria-hidden') === 'false') {
                closeModal();
            }
        });

        form.addEventListener('submit', function (event) {
            const ok = window.confirm('¿Confirmas guardar el cambio de clave para este dia?');
            if (!ok) {
                event.preventDefault();
            }
        });
    })();
</script>
@endpush

