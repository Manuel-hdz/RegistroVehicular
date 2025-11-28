@extends('layouts.app')

@push('head')
<style>
  .filters-top { display:flex; gap:8px; align-items:flex-end; flex-wrap:nowrap; overflow-x:auto; padding:6px 0; border:0; background:transparent; margin-bottom:8px; border-bottom:1px solid #e5e7eb; }
  .filters-top .filter-item { display:flex; flex-direction:column; gap:2px; flex:0 0 auto; min-width: 180px; }
  .filters-top .form-label { margin:0; font-size:12px; color:#6b7280; }
  .filters-top .form-select, .filters-top .form-control { min-width: 180px; }
  .filters-top .form-select-sm, .filters-top .form-control-sm { padding:.25rem .5rem; }
  .equal-card { height:100%; display:flex; flex-direction:column; }
  .equal-card canvas { max-width:100%; height:240px; }
  @media (max-width: 900px){ .equal-card canvas { height:200px; } }
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
  <form method="GET" class="d-flex flex-nowrap gap-2 align-items-end w-100">
    <div class="filter-item">
      <label class="form-label">Vehículo</label>
      <select name="vehicle_id" class="form-select form-select-sm">
        <option value="">Todos</option>
        @foreach($vehicles as $v)
          <option value="{{ $v->id }}" @selected((string)request('vehicle_id') === (string)$v->id)>{{ $v->identifier }}</option>
        @endforeach
      </select>
    </div>
    <div class="filter-item">
      <label class="form-label">Conductor</label>
      <select name="driver_id" class="form-select form-select-sm">
        <option value="">Todos</option>
        @foreach($drivers as $d)
          <option value="{{ $d->id }}" @selected((string)request('driver_id') === (string)$d->id)>{{ $d->name }}</option>
        @endforeach
      </select>
    </div>
    <div class="ms-auto d-flex gap-2 flex-shrink-0">
      <button class="btn btn-primary btn-sm" type="submit"><i class="bi bi-funnel me-1"></i>Aplicar</button>
      <a class="btn btn-outline-secondary btn-sm" href="{{ route('public.dashboard') }}">Limpiar</a>
    </div>
  </form>
</div>

<div class="section-title"><h4 style="margin:0">Últimos 7 días</h4></div>
<div class="row g-2" style="margin-bottom:12px;">
  <div class="col-12 col-lg-6">
    <div class="card equal-card">
      <h5 class="px-2 pt-2" style="margin:0;">Salidas por día</h5>
      <div class="p-2"><canvas id="chartDays7"></canvas></div>
    </div>
  </div>
  <div class="col-12 col-lg-6">
    <div class="card equal-card">
      <h5 class="px-2 pt-2" style="margin:0;">Top 5 equipos por salidas</h5>
      <div class="p-2"><canvas id="chartTopVehicles7"></canvas></div>
    </div>
  </div>
  <div class="col-12 col-lg-6">
    <div class="card equal-card">
      <h5 class="px-2 pt-2" style="margin:0;">Top 5 equipos por kilómetros</h5>
      <div class="p-2"><canvas id="chartTopKm7"></canvas></div>
    </div>
  </div>
  <div class="col-12 col-lg-6">
    <div class="card equal-card">
      <h5 class="px-2 pt-2" style="margin:0;">Top 5 conductores por salidas</h5>
      <div class="p-2"><canvas id="chartTopDrivers7"></canvas></div>
    </div>
  </div>
</div>

<div class="section-title">
  <h4 style="margin:0">Últimos 30 días</h4>
  <small class="text-muted">Comparativo extendido</small>
</div>
<div class="row g-2">
  <div class="col-12 col-lg-6">
    <div class="card equal-card">
      <h5 class="px-2 pt-2" style="margin:0;">Salidas por día</h5>
      <div class="p-2"><canvas id="chartDays30"></canvas></div>
    </div>
  </div>
  <div class="col-12 col-lg-6">
    <div class="card equal-card">
      <h5 class="px-2 pt-2" style="margin:0;">Top 5 equipos por salidas</h5>
      <div class="p-2"><canvas id="chartTopVehicles30"></canvas></div>
    </div>
  </div>
  <div class="col-12 col-lg-6">
    <div class="card equal-card">
      <h5 class="px-2 pt-2" style="margin:0;">Top 5 equipos por kilómetros</h5>
      <div class="p-2"><canvas id="chartTopKm30"></canvas></div>
    </div>
  </div>
  <div class="col-12 col-lg-6">
    <div class="card equal-card">
      <h5 class="px-2 pt-2" style="margin:0;">Top 5 conductores por salidas</h5>
      <div class="p-2"><canvas id="chartTopDrivers30"></canvas></div>
    </div>
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

