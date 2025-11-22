@extends('layouts.app')

@push('head-pre')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
@endpush

@push('head')
    <style>
        .filters-top {
            display: flex;
            gap: 10px;
            align-items: flex-end;
            flex-wrap: wrap;
            padding: 10px 12px;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            background: #fff;
            margin-bottom: 12px;
        }
        .filters-top .form-label { margin-bottom: 4px; font-size: 14px; color:#374151; }
        .filters-top .form-select { min-width: 220px; }
        .equal-grid { display:grid; grid-template-columns: repeat(2, minmax(0,1fr)); gap:12px; align-items:stretch; }
        .equal-card { height:100%; display:flex; flex-direction:column; }
        .equal-card canvas { max-width:100%; height:240px; }
        .section-title { display:flex; align-items:center; justify-content:space-between; margin: 6px 0 8px; }
    </style>
@endpush

@section('content')
<div class="card" style="margin-bottom:12px;">
    <div>
        <h2 style="margin:0">Dashboard Público</h2>
        <p style="margin:6px 0 0 0; color:#555;">Resumen de uso de equipos y conductores.</p>
    </div>
</div>

<div class="filters-top">
    <form method="GET" class="d-flex flex-wrap gap-2 align-items-end w-100">
        <div class="d-flex flex-column">
            <label class="form-label">Vehículo</label>
            <select name="vehicle_id" class="form-select">
                <option value="">Todos</option>
                @foreach($vehicles as $v)
                    <option value="{{ $v->id }}" @selected((string)request('vehicle_id') === (string)$v->id)>{{ $v->identifier }}</option>
                @endforeach
            </select>
        </div>
        <div class="d-flex flex-column">
            <label class="form-label">Conductor</label>
            <select name="driver_id" class="form-select">
                <option value="">Todos</option>
                @foreach($drivers as $d)
                    <option value="{{ $d->id }}" @selected((string)request('driver_id') === (string)$d->id)>{{ $d->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="ms-auto d-flex gap-2">
            <button class="btn btn-primary" type="submit"><i class="bi bi-funnel me-1"></i>Aplicar</button>
            <a class="btn btn-outline-secondary" href="{{ route('public.dashboard') }}">Limpiar</a>
        </div>
    </form>
    </div>

<div class="section-title">
    <h4 style="margin:0">Últimos 7 días</h4>
</div>
<div class="equal-grid" style="margin-bottom:12px;">
    <div class="card equal-card">
        <h5 class="px-2 pt-2" style="margin:0;">Salidas por día</h5>
        <div class="p-2"><canvas id="chartDays7"></canvas></div>
    </div>
    <div class="card equal-card">
        <h5 class="px-2 pt-2" style="margin:0;">Top 5 equipos por salidas</h5>
        <div class="p-2"><canvas id="chartTopVehicles7"></canvas></div>
    </div>
    <div class="card equal-card">
        <h5 class="px-2 pt-2" style="margin:0;">Top 5 equipos por kilómetros</h5>
        <div class="p-2"><canvas id="chartTopKm7"></canvas></div>
    </div>
    <div class="card equal-card">
        <h5 class="px-2 pt-2" style="margin:0;">Top 5 conductores por salidas</h5>
        <div class="p-2"><canvas id="chartTopDrivers7"></canvas></div>
    </div>
</div>

<div class="section-title">
    <h4 style="margin:0">Últimos 30 días</h4>
    <small class="text-muted">Comparativo extendido</small>
    </div>
<div class="equal-grid">
    <div class="card equal-card">
        <h5 class="px-2 pt-2" style="margin:0;">Salidas por día</h5>
        <div class="p-2"><canvas id="chartDays30"></canvas></div>
    </div>
    <div class="card equal-card">
        <h5 class="px-2 pt-2" style="margin:0;">Top 5 equipos por salidas</h5>
        <div class="p-2"><canvas id="chartTopVehicles30"></canvas></div>
    </div>
    <div class="card equal-card">
        <h5 class="px-2 pt-2" style="margin:0;">Top 5 equipos por kilómetros</h5>
        <div class="p-2"><canvas id="chartTopKm30"></canvas></div>
    </div>
    <div class="card equal-card">
        <h5 class="px-2 pt-2" style="margin:0;">Top 5 conductores por salidas</h5>
        <div class="p-2"><canvas id="chartTopDrivers30"></canvas></div>
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
    const days7 = @json($days7 ?? []);
    const series7 = @json($series7 ?? []);
    const days30 = @json($days30 ?? []);
    const series30 = @json($series30 ?? []);

    const tv7 = @json(($topVehicles7 ?? collect())->map(fn($r)=>[$r->vehicle?->identifier ?? 'N/A', (int)$r->total]));
    const tkm7 = @json(($topKm7 ?? collect())->map(fn($r)=>[$r->vehicle?->identifier ?? 'N/A', (int)$r->km]));
    const td7 = @json(($topDrivers7 ?? collect())->map(fn($r)=>[$r->driver?->name ?? 'N/A', (int)$r->total]));

    const tv30 = @json(($topVehicles30 ?? collect())->map(fn($r)=>[$r->vehicle?->identifier ?? 'N/A', (int)$r->total]));
    const tkm30 = @json(($topKm30 ?? collect())->map(fn($r)=>[$r->vehicle?->identifier ?? 'N/A', (int)$r->km]));
    const td30 = @json(($topDrivers30 ?? collect())->map(fn($r)=>[$r->driver?->name ?? 'N/A', (int)$r->total]));

    function lineChart(el, labels, data, label){
        if (!el || !labels?.length) return;
        new Chart(el, {
            type: 'line',
            data: { labels, datasets: [{ label, data, borderColor:'#0d6efd', backgroundColor:'rgba(13,110,253,.15)', tension:.25, fill:true }] },
            options: { responsive:true, maintainAspectRatio:false, plugins:{legend:{display:false}} }
        });
    }
    function barChart(el, rows, label){
        if (!el || !rows?.length) return;
        const labels = rows.map(r=>r[0]);
        const data = rows.map(r=>r[1]);
        new Chart(el, {
            type: 'bar',
            data: { labels, datasets: [{ label, data, backgroundColor:'#198754' }] },
            options: { responsive:true, maintainAspectRatio:false, plugins:{legend:{display:false}} }
        });
    }

    lineChart(document.getElementById('chartDays7'), days7, series7, 'Salidas');
    barChart(document.getElementById('chartTopVehicles7'), tv7, 'Salidas');
    barChart(document.getElementById('chartTopKm7'), tkm7, 'Km');
    barChart(document.getElementById('chartTopDrivers7'), td7, 'Salidas');

    lineChart(document.getElementById('chartDays30'), days30, series30, 'Salidas');
    barChart(document.getElementById('chartTopVehicles30'), tv30, 'Salidas');
    barChart(document.getElementById('chartTopKm30'), tkm30, 'Km');
    barChart(document.getElementById('chartTopDrivers30'), td30, 'Salidas');
</script>
@endpush

@endsection

