@extends('layouts.app')

@push('head-pre')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
@endpush

@push('head')
    <style>
        #dashLayout { display:flex; gap:12px; align-items:flex-start; flex-wrap:wrap; }
        #dashLayout > .main { flex: 1 1 700px; min-width:280px; }
        #dashLayout > aside {
            flex: 0 0 320px;
            max-width: 320px;
            transition: flex-basis .25s ease, max-width .25s ease, opacity .25s ease, transform .25s ease;
            opacity: 1;
            transform: translateX(0);
        }
        /* Mover filtros a la izquierda en pantallas grandes */
        @media (min-width: 992px) { #dashLayout > aside { order: -1; } }
        /* Colapsar filtros con animación */
        #dashLayout.collapsed > aside {
            flex-basis: 0 !important;
            max-width: 0 !important;
            opacity: 0;
            transform: translateX(12px);
            pointer-events: none;
        }
        /* Fade-in del contenido principal */
        @keyframes fadeIn { from { opacity: 0; transform: translateY(4px);} to { opacity: 1; transform: translateY(0);} }
        #dashLayout > .main { animation: fadeIn .25s ease; }
    </style>
@endpush

@section('content')
<div class="card">
    <div>
        <h2 style="margin:0">Dashboard Público</h2>
        <p style="margin:6px 0 0 0; color:#555;">Resumen de uso de equipos y conductores (sin iniciar sesión).</p>
    </div>
  </div>

<div id="dashLayout" class="grid" style="grid-template-columns: 1fr 320px; align-items:start; gap:12px;">
  <div class="main">
    <div class="card">
        <h3 style="margin-top:0">Salidas por día (últimos {{ $selectedDays }} días)</h3>
        <canvas id="chartByDay" height="110"></canvas>
    </div>

    <div class="grid grid-2">
        <div class="card">
            <h3 style="margin-top:0">Top 5 equipos por salidas</h3>
            <canvas id="chartTopVehicles" height="120"></canvas>
            <table class="table table-sm" style="margin-top:10px;">
                <thead><tr><th>Vehículo</th><th>Salidas</th></tr></thead>
                <tbody>
                @forelse($topVehicleByDepartures as $row)
                    <tr>
                        <td>{{ ($row->vehicle?->identifier ? $row->vehicle?->identifier . ' — ' : '') . ($row->vehicle?->plate ?? ('#'.$row->vehicle_id)) }}</td>
                        <td>{{ $row->total }}</td>
                    </tr>
                @empty
                    <tr><td colspan="2">Sin datos</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="card">
            <h3 style="margin-top:0">Top 5 equipos por kilómetros</h3>
            <canvas id="chartTopKm" height="120"></canvas>
            <table class="table table-sm" style="margin-top:10px;">
                <thead><tr><th>Vehículo</th><th>Kilómetros</th></tr></thead>
                <tbody>
                @forelse($topVehicleByKm as $row)
                    <tr>
                        <td>{{ ($row->vehicle?->identifier ? $row->vehicle?->identifier . ' — ' : '') . ($row->vehicle?->plate ?? ('#'.$row->vehicle_id)) }}</td>
                        <td>{{ (int) $row->km }}</td>
                    </tr>
                @empty
                    <tr><td colspan="2">Sin datos</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="card">
            <h3 style="margin-top:0">Top 5 conductores por salidas</h3>
            <canvas id="chartTopDrivers" height="120"></canvas>
            <table class="table table-sm" style="margin-top:10px;">
                <thead><tr><th>Conductor</th><th>Salidas</th></tr></thead>
                <tbody>
                @forelse($topDriverByDepartures as $row)
                    <tr>
                        <td>{{ $row->driver?->name ?? ('#'.$row->driver_id) }}</td>
                        <td>{{ $row->total }}</td>
                    </tr>
                @empty
                    <tr><td colspan="2">Sin datos</td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
    </div>
  </div>

  <aside>
    <div class="card" style="position: sticky; top: 86px;">
        <h3 style="margin-top:0; font-size:18px;">Filtros</h3>
        <form method="GET" class="grid" style="gap:10px;">
            <div>
                <label style="font-size:14px;">Vehículo</label>
                <select name="vehicle_id" class="form-select" style="padding:8px 10px;">
                    <option value="">Todos</option>
                    @foreach($vehicles as $v)
                        <option value="{{ $v->id }}" @selected((string)request('vehicle_id') === (string)$v->id)>{{ $v->identifier ? $v->identifier . ' — ' : '' }}{{ $v->plate }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label style="font-size:14px;">Conductor</label>
                <select name="driver_id" class="form-select" style="padding:8px 10px;">
                    <option value="">Todos</option>
                    @foreach($drivers as $d)
                        <option value="{{ $d->id }}" @selected((string)request('driver_id') === (string)$d->id)>{{ $d->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label style="font-size:14px;">Periodo</label>
                <select name="days" class="form-select" style="padding:8px 10px;">
                    <option value="7" @selected((int)$selectedDays===7)>7 días</option>
                    <option value="30" @selected((int)$selectedDays===30)>30 días</option>
                    <option value="90" @selected((int)$selectedDays===90)>90 días</option>
                </select>
            </div>
            <div class="row" style="gap:8px;">
                <button class="btn btn-primary" type="submit">Aplicar</button>
                <a class="btn btn-secondary" href="{{ route('public.dashboard') }}">Limpiar</a>
            </div>
        </form>
    </div>
  </aside>
</div>

@push('scripts')
<script>
  (function(){
    const root = document.getElementById('dashLayout');
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'btn btn-secondary';
    btn.innerHTML = '<i class="bi bi-funnel" style="margin-right:6px;"></i><span class="lbl"></span>';
    const hdr = document.querySelectorAll('.card')[0]?.querySelector('.row') || document.querySelectorAll('.card')[0];
    // Insertar botón al encabezado principal
    if (hdr) {
      const wrap = document.createElement('div');
      wrap.style.marginTop = '8px';
      wrap.appendChild(btn);
      hdr.appendChild(wrap);
    }
    function setLabel(){
      const text = root.classList.contains('collapsed') ? 'Mostrar filtros' : 'Ocultar filtros';
      const span = btn.querySelector('.lbl');
      if (span) span.textContent = text; else btn.textContent = text;
    }
    // Estado inicial desde localStorage
    if (localStorage.getItem('dashFiltersCollapsed') === '1') {
      root.classList.add('collapsed');
    }
    setLabel();
    btn.addEventListener('click', function(){
      const willCollapse = !root.classList.contains('collapsed');
      root.classList.toggle('collapsed', willCollapse);
      localStorage.setItem('dashFiltersCollapsed', willCollapse ? '1' : '0');
      setLabel();
    });
  })();
</script>
@endpush

<div class="card">
    <h3 style="margin-top:0">Salidas por día (últimos {{ $selectedDays }} días)</h3>
    <canvas id="chartByDay" height="110"></canvas>
</div>

<div class="grid grid-2">
    <div class="card">
        <h3 style="margin-top:0">Top 5 equipos por salidas</h3>
        <canvas id="chartTopVehicles" height="120"></canvas>
        <table class="table table-sm" style="margin-top:10px;">
            <thead><tr><th>Vehículo</th><th>Salidas</th></tr></thead>
            <tbody>
            @forelse($topVehicleByDepartures as $row)
                <tr>
                    <td>{{ $row->vehicle?->plate ?? ('#'.$row->vehicle_id) }}</td>
                    <td>{{ $row->total }}</td>
                </tr>
            @empty
                <tr><td colspan="2">Sin datos</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="card">
        <h3 style="margin-top:0">Top 5 equipos por kilómetros</h3>
        <canvas id="chartTopKm" height="120"></canvas>
        <table class="table table-sm" style="margin-top:10px;">
            <thead><tr><th>Vehículo</th><th>Kilómetros</th></tr></thead>
            <tbody>
            @forelse($topVehicleByKm as $row)
                <tr>
                    <td>{{ $row->vehicle?->plate ?? ('#'.$row->vehicle_id) }}</td>
                    <td>{{ (int) $row->km }}</td>
                </tr>
            @empty
                <tr><td colspan="2">Sin datos</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>

    <div class="card">
        <h3 style="margin-top:0">Top 5 conductores por salidas</h3>
        <canvas id="chartTopDrivers" height="120"></canvas>
        <table class="table table-sm" style="margin-top:10px;">
            <thead><tr><th>Conductor</th><th>Salidas</th></tr></thead>
            <tbody>
            @forelse($topDriverByDepartures as $row)
                <tr>
                    <td>{{ $row->driver?->name ?? ('#'.$row->driver_id) }}</td>
                    <td>{{ $row->total }}</td>
                </tr>
            @empty
                <tr><td colspan="2">Sin datos</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <script>
        const labelsDays = @json($days);
        const dataDays = @json($seriesDays);

        const ctxDay = document.getElementById('chartByDay');
        if (ctxDay) {
            new Chart(ctxDay, {
                type: 'line',
                data: {
                    labels: labelsDays,
                    datasets: [{
                        label: 'Salidas',
                        data: dataDays,
                        borderColor: '#006847',
                        backgroundColor: 'rgba(0,104,71,.15)',
                        tension: 0.25,
                        fill: true
                    }]
                },
                options: {
                    scales: {
                        y: { beginAtZero: true, precision:0 }
                    }
                }
            });
        }

        const topVehicles = @json($topVehicleByDepartures->map(fn($r) => [
            (($r->vehicle?->identifier ? $r->vehicle?->identifier.' — ' : '') . ($r->vehicle?->plate ?? ('#'.$r->vehicle_id))), (int) $r->total
        ]));
        const topKm = @json($topVehicleByKm->map(fn($r) => [
            (($r->vehicle?->identifier ? $r->vehicle?->identifier.' — ' : '') . ($r->vehicle?->plate ?? ('#'.$r->vehicle_id))), (int) $r->km
        ]));
        const topDrivers = @json($topDriverByDepartures->map(fn($r) => [
            $r->driver?->name ?? ('#'.$r->driver_id), (int) $r->total
        ]));

        function barChart(canvasId, rows, label, color){
            const el = document.getElementById(canvasId);
            if(!el) return;
            const labels = rows.map(r => r[0]);
            const data = rows.map(r => r[1]);
            new Chart(el, {
                type: 'bar',
                data: { labels, datasets: [{ label, data, backgroundColor: color }] },
                options: { scales: { y: { beginAtZero: true } } }
            });
        }

        barChart('chartTopVehicles', topVehicles, 'Salidas', '#FFCD11');
        barChart('chartTopKm', topKm, 'Kilómetros', '#3b82f6');
        barChart('chartTopDrivers', topDrivers, 'Salidas', '#10b981');
    </script>
@endpush
