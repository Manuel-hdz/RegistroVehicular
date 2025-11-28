@extends('layouts.app')

@section('content')
@push('head-pre')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
@endpush
@push('head')
<style>
  /* Ocultar columnas pesadas en mÃ³viles para la tabla de abiertos */
  @media (max-width: 575.98px){
    table.movements-open thead th:nth-child(4),
    table.movements-open thead th:nth-child(5),
    table.movements-open thead th:nth-child(7),
    table.movements-open thead th:nth-child(8),
    table.movements-open tbody td:nth-child(4),
    table.movements-open tbody td:nth-child(5),
    table.movements-open tbody td:nth-child(7),
    table.movements-open tbody td:nth-child(8){
      display: none !important;
    }
  }
</style>
@endpush
<div class="card">
    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2">
        <h2 class="m-0" style="font-size:20px;">Movimientos</h2>
        <a href="{{ route('movements.create') }}" class="btn btn-primary btn-sm">Registrar Salida</a>
    </div>
</div>

<div class="card">
    <h3 class="mt-0" style="font-size:18px;">Abiertos</h3>
    <div class="table-responsive">
    <table class="table table-striped align-middle movements-open">
        <thead>
            <tr>
                <th>#</th>
                <th>Vehículo</th>
                <th>Conductor</th>
                <th class="d-none d-sm-table-cell">Odómetro Salida</th>
                <th>Combustible Salida</th>
                <th>Fecha/Hora Salida</th>
                <th>Destino</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($open as $m)
                <tr>
                    <td>{{ $m->id }}</td>
                    <td>{{ $m->vehicle->identifier }}</td>
                    <td>{{ $m->driver->name }}</td>
                    <td>{{ $m->odometer_out }}</td>
                    <td>{{ $m->fuel_out }}%</td>
                    <td>{{ $m->departed_at->format('Y-m-d H:i') }}</td>
                    <td>{{ $m->destination }}</td>
                    <td>
                        <a class="btn btn-warning" href="{{ route('movements.checkin.form', $m) }}">Registrar Entrada</a>
                        @auth
                            @if(auth()->user()->role === 'superadmin')
                                <div style="margin-top:6px; display:flex; gap:6px; align-items:center;">
                                    <a class="btn btn-secondary btn-icon" href="{{ route('movements.edit', $m) }}" title="Editar" aria-label="Editar">
                                        <i class="bi bi-pencil-square" aria-hidden="true"></i>
                                    </a>
                                    <form action="{{ route('movements.cancel', $m) }}" method="POST" style="display:inline;" onsubmit="return confirm('Â¿Seguro que deseas cancelar esta salida?');">
                                        @csrf
                                        @method('PUT')
                                        <button class="btn btn-secondary btn-icon" type="submit" title="Cancelar" aria-label="Cancelar">
                                            <i class="bi bi-x-circle" aria-hidden="true" style="color:#dc2626;"></i>
                                        </button>
                                    </form>
                                </div>
                            @endif
                        @endauth
                    </td>
                </tr>
            @empty
                <tr><td colspan="7">Sin movimientos abiertos</td></tr>
            @endforelse
        </tbody>
    </table>
    
</div>

<div class="card">
    <h3 class="mt-0" style="font-size:18px;">Cerrados Recientes</h3>
    <div class="table-responsive">
    <table class="table table-striped align-middle">
        <thead>
            <tr>
                <th>#</th>
                <th>Vehículo</th>
                <th>Conductor</th>
                <th>Salida</th>
                <th>Entrada</th>
                <th>Km Recorridos</th>
            </tr>
        </thead>
        <tbody>
            @forelse($recentClosed as $m)
                <tr>
                    <td>{{ $m->id }}</td>
                    <td>{{ $m->vehicle->identifier }}</td>
                    <td>{{ $m->driver->name }}</td>
                    <td>{{ $m->departed_at?->format('Y-m-d H:i') }}</td>
                    <td>{{ $m->arrived_at?->format('Y-m-d H:i') }}</td>
                    <td>{{ ($m->odometer_in ?? 0) - ($m->odometer_out ?? 0) }}</td>
                </tr>
            @empty
                <tr><td colspan="5">Sin historial</td></tr>
            @endforelse
        </tbody>
    </table>
    </div>
</div>
@endsection


