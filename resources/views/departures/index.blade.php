@extends('layouts.app')

@section('content')
@push('head-pre')
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
@endpush
@push('head')
<style>
  #departuresLayout { display:flex; gap:12px; align-items:flex-start; flex-wrap:wrap; }
  #departuresLayout > .main { flex: 1 1 700px; min-width:280px; }
  #departuresLayout > aside {
    flex: 0 0 320px;
    max-width: 320px;
    transition: flex-basis .25s ease, max-width .25s ease, opacity .25s ease, transform .25s ease;
    opacity: 1;
    transform: translateX(0);
  }
  @media (min-width: 992px) { #departuresLayout > aside { order: -1; } }
  #departuresLayout.collapsed > aside {
    flex-basis: 0 !important;
    max-width: 0 !important;
    opacity: 0;
    transform: translateX(12px);
    pointer-events: none;
  }
  @keyframes fadeIn { from { opacity: 0; transform: translateY(4px);} to { opacity: 1; transform: translateY(0);} }
  #departuresLayout > .main { animation: fadeIn .25s ease; }
</style>
@endpush

<div class="card">
    <div class="row" style="justify-content: space-between; align-items: end; gap:10px;">
        <div>
            <h2 style="margin:0">Registros de Salidas</h2>
            <small style="color:#555;">Filtra por fecha, vehículo o conductor</small>
        </div>
        <div class="row" style="gap:8px;">
            <button type="button" class="btn btn-secondary" id="btnToggleFiltersDeps">
                <i class="bi bi-funnel" style="margin-right:6px;"></i>
                <span class="lbl">Ocultar filtros</span>
            </button>
            <a class="btn btn-secondary" href="{{ route('departures.export', request()->query()) }}">Exportar CSV</a>
        </div>
    </div>
</div>

<div id="departuresLayout">
  <div class="card main">
    @if ($departures->total() > 0)
        <div style="margin-bottom:10px; color:#555; font-size:14px; text-align:right;">
            Mostrando {{ $departures->firstItem() }} a {{ $departures->lastItem() }} de {{ $departures->total() }} resultados
            @if ($departures->hasPages())
                <span> · Página {{ $departures->currentPage() }} de {{ $departures->lastPage() }} ·
                @if ($departures->onFirstPage())
                    <span style="opacity:.6;">Anterior</span>
                @else
                    <a href="{{ $departures->previousPageUrl() }}">Anterior</a>
                @endif
                <span> | </span>
                @if ($departures->hasMorePages())
                    <a href="{{ $departures->nextPageUrl() }}">Siguiente</a>
                @else
                    <span style="opacity:.6;">Siguiente</span>
                @endif
                </span>
            @endif
        </div>
    @endif
    <table>
        <thead>
            <tr>
                <th>Fecha/Hora Salida</th>
                <th>Vehículo</th>
                <th>Conductor</th>
                <th>Registró</th>
                <th>Estatus</th>
                <th>Destino</th>
            </tr>
        </thead>
        <tbody>
            @forelse($departures as $m)
                <tr>
                    <td>{{ $m->departed_at?->format('Y-m-d H:i') }}</td>
                    <td>{{ $m->vehicle->identifier ? $m->vehicle->identifier . ' — ' : '' }}{{ $m->vehicle->plate }}</td>
                    <td>{{ $m->driver->name }}</td>
                    <td>{{ $m->guardOut?->name ?? '—' }}</td>
                    <td>
                        @switch($m->status)
                            @case('closed')
                                <span style="display:inline-block; padding:4px 8px; border-radius:12px; background:#e9fff1; border:1px solid #a7f3d0; color:#065f46;">Completado</span>
                                @break
                            @case('cancelled')
                                <span style="display:inline-block; padding:4px 8px; border-radius:12px; background:#fff7ed; border:1px solid #fed7aa; color:#9a3412;">Cancelado</span>
                                @break
                            @default
                                <span style="display:inline-block; padding:4px 8px; border-radius:12px; background:#eff6ff; border:1px solid #bfdbfe; color:#1d4ed8;">Abierto</span>
                        @endswitch
                    </td>
                    <td>{{ $m->destination }}</td>
                </tr>
            @empty
                <tr><td colspan="6">Sin registros de salidas</td></tr>
            @endforelse
        </tbody>
    </table>

    @if ($departures->total() > 0)
        <div style="margin-top:12px; color:#555; font-size:14px; text-align:right;">
            Mostrando {{ $departures->firstItem() }} a {{ $departures->lastItem() }} de {{ $departures->total() }} resultados
        </div>
        @if ($departures->hasPages())
            <div style="margin-top:6px; color:#555; font-size:14px; text-align:right;">
                Página {{ $departures->currentPage() }} de {{ $departures->lastPage() }} ·
                @if ($departures->onFirstPage())
                    <span style="opacity:.6;">Anterior</span>
                @else
                    <a href="{{ $departures->previousPageUrl() }}">Anterior</a>
                @endif
                <span> | </span>
                @if ($departures->hasMorePages())
                    <a href="{{ $departures->nextPageUrl() }}">Siguiente</a>
                @else
                    <span style="opacity:.6;">Siguiente</span>
                @endif
            </div>
        @endif
    @endif
  </div>

  <aside>
    <div class="card" style="position: sticky; top: 86px;">
        <h3 style="margin-top:0; font-size:18px;">Filtros</h3>
        <form method="GET" class="grid grid-3" style="gap:10px;">
            <div>
                <label style="font-size:14px;">Desde</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}">
            </div>
            <div>
                <label style="font-size:14px;">Hasta</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}">
            </div>
            <div>
                <label style="font-size:14px;">Destino</label>
                <input type="text" name="destination" value="{{ request('destination') }}" placeholder="Texto a buscar">
            </div>
            <div>
                <label style="font-size:14px;">Vehículo</label>
                <select name="vehicle_id">
                    <option value="">Todos</option>
                    @foreach($vehicles as $v)
                        <option value="{{ $v->id }}" @selected(request('vehicle_id')==$v->id)>{{ $v->identifier ? $v->identifier . ' — ' : '' }}{{ $v->plate }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label style="font-size:14px;">Conductor</label>
                <select name="driver_id">
                    <option value="">Todos</option>
                    @foreach($drivers as $d)
                        <option value="{{ $d->id }}" @selected(request('driver_id')==$d->id)>{{ $d->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label style="font-size:14px;">Estatus</label>
                <select name="status">
                    @foreach($statuses as $k=>$label)
                        <option value="{{ $k }}" @selected(request('status','')===$k)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div class="row" style="gap:8px; grid-column: 1/-1;">
                <button class="btn btn-primary" type="submit">Aplicar</button>
                <a class="btn btn-secondary" href="{{ route('departures.index') }}">Limpiar</a>
            </div>
        </form>
    </div>
  </aside>
</div>

@push('scripts')
<script>
  (function(){
    const root = document.getElementById('departuresLayout');
    const btn = document.getElementById('btnToggleFiltersDeps');
    if (!root || !btn) return;
    function setLabel(){
      const lbl = root.classList.contains('collapsed') ? 'Mostrar filtros' : 'Ocultar filtros';
      const span = btn.querySelector('.lbl');
      if (span) span.textContent = lbl; else btn.innerHTML = lbl;
    }
    if (localStorage.getItem('depsFiltersCollapsed') === '1') {
      root.classList.add('collapsed');
    }
    setLabel();
    btn.addEventListener('click', function(){
      const willCollapse = !root.classList.contains('collapsed');
      root.classList.toggle('collapsed', willCollapse);
      localStorage.setItem('depsFiltersCollapsed', willCollapse ? '1' : '0');
      setLabel();
    });
  })();
</script>
@endpush
@endsection
