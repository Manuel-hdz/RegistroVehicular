@extends('layouts.app')

@section('content')
@push('head-pre')
<link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endpush
@push('head')
<style>
  /* Estilos mínimos; sin filtros en header */
  .table-responsive { width:100%; overflow-x:auto; -webkit-overflow-scrolling: touch; }
  @media (max-width: 640px){
    #departuresTable thead th, #departuresTable tbody td { font-size: 14px; padding: 8px; }
  }

  /* Compactar controles de DataTables (Mostrar y Buscar) */
  .dataTables_wrapper .dataTables_length label,
  .dataTables_wrapper .dataTables_filter label {
    display: flex;
    align-items: center;
    gap: 6px;
    margin: 0;
    font-size: 14px;
    color: #374151;
  }
  .dataTables_wrapper .dataTables_length select,
  .dataTables_wrapper .dataTables_filter input {
    height: 30px;
    padding: .25rem .5rem;
    font-size: .875rem; /* 14px */
    border: 1px solid #d1d5db;
    border-radius: .375rem;
  }
  .dataTables_wrapper .dataTables_filter input { width: 220px; }
  @media (max-width: 768px){
    .dataTables_wrapper .dataTables_filter input { width: 160px; }
    .dataTables_wrapper .dataTables_length select { width: 90px; }
  }
</style>
@endpush

<div class="card">
    <div class="row" style="justify-content: space-between; align-items: end; gap:10px;">
        <div>
            <h2 style="margin:0">Registros de Salidas</h2>
            <small style="color:#555;">Filtra por fecha, vehículo o conductor</small>
        </div>
        <div class="row" style="gap:8px;">
            <a class="btn btn-secondary btn-sm" href="{{ route('departures.export', request()->query()) }}">Exportar CSV</a>
            <a class="btn btn-secondary btn-sm" href="{{ route('departures.export.excel', request()->query()) }}">Exportar Excel</a>
        </div>
    </div>
</div>

<div class="card">
  <div class="table-responsive">
    <table id="departuresTable" class="display" style="width:100%">
        <thead>
            <tr>
                <th>#</th>
                <th>Fecha/Hora Salida</th>
                <th>Vehículo</th>
                <th>Conductor</th>
                <th>Registró</th>
                <th>Estatus</th>
                <th>Destino</th>
                @auth
                    @if(auth()->user()->role === 'superadmin')
                        <th>Acciones</th>
                    @endif
                @endauth
            </tr>
        </thead>
        <tbody>
            @forelse($departures as $m)
                <tr>
                    <td>{{ $m->id }}</td>
                    <td>{{ $m->departed_at?->format('Y-m-d H:i') }}</td>
                    <td>{{ $m->vehicle->identifier }}</td>
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
                    @auth
                        @if(auth()->user()->role === 'superadmin')
                            <td style="display:flex; gap:6px; align-items:center;">
                                <a class="btn btn-secondary btn-icon" href="{{ route('movements.edit', $m) }}" title="Editar" aria-label="Editar">
                                    <i class="bi bi-pencil-square" aria-hidden="true"></i>
                                </a>
                                @if($m->status === 'open')
                                    <form action="{{ route('movements.cancel', $m) }}" method="POST" style="display:inline;" onsubmit="return confirm('¿Seguro que deseas cancelar esta salida?');">
                                        @csrf
                                        @method('PUT')
                                        <button class="btn btn-secondary btn-icon" type="submit" title="Cancelar" aria-label="Cancelar">
                                            <i class="bi bi-x-circle" aria-hidden="true" style="color:#dc2626;"></i>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        @endif
                    @endauth
                </tr>
            @empty
                <tr><td colspan="6">Sin registros de salidas</td></tr>
            @endforelse
        </tbody>
    </table>
  </div>
</div>

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script>
  // Inicialización simple de DataTables sin filtros por columna
  $(function(){
      if ($.fn.dataTable && $.fn.dataTable.ext && $.fn.dataTable.ext.pager) {
          $.fn.dataTable.ext.pager.numbers_length = 5; // mostrar 5 números
      }
      var $table = $('#departuresTable');
      var hasActions = $table.find('thead th').last().text().trim() === 'Acciones';
      $table.DataTable({
          pageLength: 25,
          order: [[0,'desc']],
          language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' },
          columnDefs: hasActions ? [{ targets: -1, orderable: false, searchable: false }] : []
      });
  });
</script>
@endpush
@endsection
