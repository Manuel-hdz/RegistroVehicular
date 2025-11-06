@extends('layouts.app')

@section('content')
<div class="card">
    <div class="row" style="justify-content: space-between;">
        <h2 style="margin:0">Movimientos</h2>
        <a href="{{ route('movements.create') }}" class="btn btn-primary">Registrar Salida</a>
    </div>
</div>

<div class="card">
    <h3 style="margin-top:0">Abiertos</h3>
    <table>
        <thead>
            <tr>
                <th>Vehículo</th>
                <th>Conductor</th>
                <th>Odómetro Salida</th>
                <th>Combustible Salida</th>
                <th>Fecha/Hora Salida</th>
                <th>Destino</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($open as $m)
                <tr>
                    <td>{{ $m->vehicle->plate }}</td>
                    <td>{{ $m->driver->name }}</td>
                    <td>{{ $m->odometer_out }}</td>
                    <td>{{ $m->fuel_out }}%</td>
                    <td>{{ $m->departed_at->format('Y-m-d H:i') }}</td>
                    <td>{{ $m->destination }}</td>
                    <td>
                        <a class="btn btn-warning" href="{{ route('movements.checkin.form', $m) }}">Registrar Entrada</a>
                        @auth
                            @if(auth()->user()->role === 'superadmin')
                                <a class="btn btn-secondary" href="{{ route('movements.edit', $m) }}">Editar</a>
                                <form action="{{ route('movements.cancel', $m) }}" method="POST" style="display:inline;">
                                    @csrf
                                    @method('PUT')
                                    <button class="btn btn-secondary" type="submit">Cancelar</button>
                                </form>
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
    <h3 style="margin-top:0">Cerrados Recientes</h3>
    <table>
        <thead>
            <tr>
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
                    <td>{{ $m->vehicle->plate }}</td>
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
@endsection
