@extends('layouts.app')

@section('content')
@push('head')
<style>
    .warehouse-report-grid { display:grid; grid-template-columns:repeat(4, minmax(0, 1fr)); gap:16px; }
    .warehouse-consumption-grid { display:grid; grid-template-columns:repeat(2, minmax(0, 1fr)); gap:18px; margin-bottom:18px; }
    .warehouse-consumption-chart { display:block; width:100%; min-height:280px; }
    @media (max-width: 991.98px) { .warehouse-report-grid { grid-template-columns:repeat(2, minmax(0, 1fr)); } }
    @media (max-width: 767.98px) { .warehouse-consumption-grid { grid-template-columns:minmax(0, 1fr); } }
    @media (max-width: 575.98px) { .warehouse-report-grid { grid-template-columns:minmax(0, 1fr); } }
</style>
@endpush
<div class="card">
    <div style="display:flex; justify-content:space-between; align-items:flex-start; gap:12px; flex-wrap:wrap;">
        <div>
            <h2 style="margin:0;">Reportes de almacén</h2>
            <p style="margin:6px 0 0; color:#6b7280;">Entradas y salidas de <strong>{{ $selectedCostCenter->name }}</strong> segmentadas por fecha.</p>
        </div>
        <a class="btn btn-secondary" href="{{ route('warehouse.movements', ['cost_center_id' => $selectedCostCenter->id]) }}">Volver a entradas y salidas</a>
    </div>
</div>

<div class="card">
    <form method="GET" action="{{ route('warehouse.reports.index') }}" class="warehouse-report-grid">
        <div>
            <label for="reportCostCenter">Centro de costos</label>
            <select id="reportCostCenter" name="cost_center_id">
                @foreach($costCenters as $costCenter)
                    <option value="{{ $costCenter->id }}" @selected($selectedCostCenter->id === $costCenter->id)>{{ $costCenter->name }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="reportDateFrom">Desde</label>
            <input id="reportDateFrom" type="date" name="date_from" value="{{ $filters['date_from'] }}" required>
        </div>
        <div>
            <label for="reportDateTo">Hasta</label>
            <input id="reportDateTo" type="date" name="date_to" value="{{ $filters['date_to'] }}" required>
        </div>
        <div>
            <label for="reportMovementType">Movimientos</label>
            <select id="reportMovementType" name="movement_type">
                <option value="all" @selected($filters['movement_type'] === 'all')>Entradas y salidas</option>
                <option value="entries" @selected($filters['movement_type'] === 'entries')>Sólo entradas</option>
                <option value="exits" @selected($filters['movement_type'] === 'exits')>Sólo salidas</option>
            </select>
        </div>
        <div style="grid-column:1/-1; display:flex; gap:10px; flex-wrap:wrap;">
            <button class="btn btn-primary" type="submit"><i class="bi bi-funnel"></i><span>Generar reporte</span></button>
            <a class="btn btn-secondary" href="{{ route('warehouse.reports.export', [
                'cost_center_id' => $selectedCostCenter->id,
                'date_from' => $filters['date_from'],
                'date_to' => $filters['date_to'],
                'movement_type' => $filters['movement_type'],
            ]) }}"><i class="bi bi-file-earmark-spreadsheet"></i><span>Descargar CSV</span></a>
            <a class="btn btn-secondary" href="{{ route('warehouse.reports.export-excel', [
                'cost_center_id' => $selectedCostCenter->id,
                'date_from' => $filters['date_from'],
                'date_to' => $filters['date_to'],
                'movement_type' => $filters['movement_type'],
            ]) }}"><i class="bi bi-file-earmark-excel"></i><span>Descargar Excel</span></a>
        </div>
    </form>
</div>

<div class="warehouse-report-grid" style="margin-bottom:18px;">
    <div class="card" style="margin:0;"><small style="color:#6b7280; font-weight:800;">ENTRADAS</small><strong style="font-size:1.8rem;">{{ number_format($entryCount) }}</strong></div>
    <div class="card" style="margin:0;"><small style="color:#6b7280; font-weight:800;">UNIDADES RECIBIDAS</small><strong style="font-size:1.8rem;">{{ number_format($entryUnits, 2) }}</strong></div>
    <div class="card" style="margin:0;"><small style="color:#6b7280; font-weight:800;">SALIDAS</small><strong style="font-size:1.8rem;">{{ number_format($exitCount) }}</strong></div>
    <div class="card" style="margin:0;"><small style="color:#6b7280; font-weight:800;">UNIDADES ENTREGADAS</small><strong style="font-size:1.8rem;">{{ number_format($exitUnits, 2) }}</strong></div>
</div>

<div class="warehouse-consumption-grid">
    <div class="card" style="margin:0;">
        <h3 style="margin:0;">Materiales más consumidos esta semana</h3>
        <p style="margin:6px 0 14px; color:#6b7280;">{{ $consumptionPeriods['week'] }} · salidas de {{ $selectedCostCenter->name }}</p>
        <canvas id="weeklyConsumptionChart" class="warehouse-consumption-chart" role="img" aria-label="Materiales más consumidos durante la semana actual"></canvas>
    </div>
    <div class="card" style="margin:0;">
        <h3 style="margin:0;">Materiales más consumidos este mes</h3>
        <p style="margin:6px 0 14px; color:#6b7280;">{{ $consumptionPeriods['month'] }} · salidas de {{ $selectedCostCenter->name }}</p>
        <canvas id="monthlyConsumptionChart" class="warehouse-consumption-chart" role="img" aria-label="Materiales más consumidos durante el mes actual"></canvas>
    </div>
</div>

@if($entries)
    <div class="card">
        <h3 style="margin-top:0;">Entradas del periodo</h3>
        <div class="table-responsive">
            <table class="table table-striped align-middle" style="width:100%;">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Folio</th>
                        <th>Tipo</th>
                        <th>Material</th>
                        <th>Características</th>
                        <th>Ubicación</th>
                        <th>Registró</th>
                        <th style="text-align:right;">Cantidad</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($entries as $entryMaterial)
                        <tr>
                            <td>{{ $entryMaterial->entry?->entry_date?->format('Y-m-d H:i') }}</td>
                            <td>{{ $entryMaterial->entry?->entry_key ?? 'Sin folio' }}</td>
                            <td>{{ $entryMaterial->entry?->entry_type ?? 'Sin tipo' }}</td>
                            <td><strong>{{ $entryMaterial->part?->name ?? 'Material eliminado' }}</strong><br><small>{{ $entryMaterial->part?->clave ?? 'Sin clave' }}</small></td>
                            <td>{{ implode(', ', $entryMaterial->part?->characteristics ?? []) ?: 'Sin características' }}</td>
                            <td>{{ $entryMaterial->location?->name ?? 'Sin ubicación' }}</td>
                            <td>{{ $entryMaterial->entry?->registeredBy?->name ?? $entryMaterial->entry?->registered_by_username ?? 'Registro previo' }}</td>
                            <td style="text-align:right; font-weight:800;">{{ number_format((float) $entryMaterial->quantity, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="8" style="text-align:center; color:#6b7280;">No hay entradas en el periodo seleccionado.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="margin-top:12px;">{{ $entries->links() }}</div>
    </div>
@endif

@if($exits)
    <div class="card">
        <h3 style="margin-top:0;">Salidas del periodo</h3>
        <div class="table-responsive">
            <table class="table table-striped align-middle" style="width:100%;">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Vale</th>
                        <th>Material</th>
                        <th>Despachó / llevó</th>
                        <th>Destino</th>
                        <th>Responsable</th>
                        <th>Registró</th>
                        <th>Estatus</th>
                        <th style="text-align:right;">Cantidad</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($exits as $exit)
                        <tr>
                            <td>{{ $exit->exit_date?->format('Y-m-d H:i') }}</td>
                            <td>{{ $exit->voucher_number ?? 'Sin vale' }}</td>
                            <td><strong>{{ $exit->part?->name ?? 'Material eliminado' }}</strong><br><small>{{ $exit->part?->clave ?? 'Sin clave' }}</small></td>
                            <td>{{ $exit->dispatched_by ?: $exit->source ?: 'Sin registrar' }}<br><small>{{ $exit->carried_by ?: 'Sin registrar' }}</small></td>
                            <td>{{ $exit->destination ?: 'Sin registrar' }}</td>
                            <td>{{ $exit->responsible ?: 'Sin registrar' }}</td>
                            <td>{{ $exit->registeredBy?->name ?? $exit->registered_by_username ?? 'Registro previo' }}</td>
                            <td>{{ $exit->status === 'entregado' ? 'Entregado' : 'En curso' }}</td>
                            <td style="text-align:right; font-weight:800;">{{ number_format((float) $exit->quantity, 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="9" style="text-align:center; color:#6b7280;">No hay salidas en el periodo seleccionado.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div style="margin-top:12px;">{{ $exits->links() }}</div>
    </div>
@endif

@push('scripts')
<script>
    (function () {
        const chartDefinitions = [
            { id: 'weeklyConsumptionChart', rows: @json($weeklyConsumption) },
            { id: 'monthlyConsumptionChart', rows: @json($monthlyConsumption) },
        ];

        function fitText(context, value, maxWidth) {
            if (context.measureText(value).width <= maxWidth) return value;
            let shortened = value;
            while (shortened.length > 4 && context.measureText(shortened + '…').width > maxWidth) {
                shortened = shortened.slice(0, -1);
            }
            return shortened + '…';
        }

        function drawBar(context, x, y, width, height, radius) {
            if (typeof context.roundRect === 'function') {
                context.beginPath();
                context.roundRect(x, y, width, height, radius);
                context.fill();
                return;
            }

            context.fillRect(x, y, width, height);
        }

        function drawChart(definition) {
            const canvas = document.getElementById(definition.id);
            if (!canvas) return;

            const rows = Array.isArray(definition.rows) ? definition.rows : [];
            const ratio = window.devicePixelRatio || 1;
            const width = Math.max(280, canvas.getBoundingClientRect().width);
            const height = Math.max(280, rows.length * 52 + 34);
            canvas.style.height = height + 'px';
            canvas.width = Math.round(width * ratio);
            canvas.height = Math.round(height * ratio);

            const context = canvas.getContext('2d');
            context.setTransform(ratio, 0, 0, ratio, 0, 0);
            context.clearRect(0, 0, width, height);

            if (!rows.length) {
                context.fillStyle = '#6b7280';
                context.font = '700 14px Manrope, Segoe UI, sans-serif';
                context.textAlign = 'center';
                context.fillText('Sin salidas registradas en este periodo', width / 2, height / 2);
                return;
            }

            const maxQuantity = Math.max(...rows.map(row => Number(row.quantity) || 0), 1);
            const formatter = new Intl.NumberFormat('es-MX', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            const left = 12;
            const availableWidth = width - 24;

            rows.forEach(function (row, index) {
                const y = 12 + index * 52;
                const requests = Number(row.requests) || 0;
                const meta = formatter.format(Number(row.quantity) || 0) + ' unidades · ' + requests + (requests === 1 ? ' solicitud' : ' solicitudes');
                const material = row.label + (row.key ? ' · ' + row.key : '');

                context.font = '700 13px Manrope, Segoe UI, sans-serif';
                context.fillStyle = '#1f2937';
                context.textAlign = 'left';
                context.fillText(fitText(context, material, Math.max(90, availableWidth * .55)), left, y + 12);

                context.font = '700 11px Manrope, Segoe UI, sans-serif';
                context.fillStyle = '#4b5563';
                context.textAlign = 'right';
                context.fillText(meta, width - 12, y + 12);

                context.fillStyle = '#e5e7eb';
                drawBar(context, left, y + 23, availableWidth, 12, 6);

                const barWidth = Math.max(6, availableWidth * ((Number(row.quantity) || 0) / maxQuantity));
                const gradient = context.createLinearGradient(left, 0, left + barWidth, 0);
                gradient.addColorStop(0, '#006847');
                gradient.addColorStop(1, '#16a34a');
                context.fillStyle = gradient;
                drawBar(context, left, y + 23, barWidth, 12, 6);
            });
        }

        function drawAllCharts() {
            chartDefinitions.forEach(drawChart);
        }

        drawAllCharts();
        let resizeTimer = null;
        window.addEventListener('resize', function () {
            window.clearTimeout(resizeTimer);
            resizeTimer = window.setTimeout(drawAllCharts, 120);
        });
    })();
</script>
@endpush
@endsection
