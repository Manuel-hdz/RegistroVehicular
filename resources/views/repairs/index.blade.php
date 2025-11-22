@extends('layouts.app')

@section('content')
<div class="card">
    <div class="row" style="justify-content: space-between; align-items:end;">
        <h2 style="margin:0">Reparaciones</h2>
        <a href="{{ route('repairs.create') }}" class="btn btn-primary">Nueva Reparación</a>
    </div>
</div>

<div class="card">
    <form method="GET" class="row" style="gap:10px; align-items:end;">
        <div>
            <label>Unidad</label>
            <select name="vehicle_id">
                <option value="">Todas</option>
                @foreach($vehicles as $v)
                    <option value="{{ $v->id }}" @selected(request('vehicle_id')==$v->id)>{{ $v->identifier }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <button class="btn btn-secondary" type="submit">Filtrar</button>
            <a class="btn btn-link" href="{{ route('repairs.index') }}">Limpiar</a>
        </div>
    </form>
    <table>
        <thead><tr><th>#</th><th>Unidad</th><th>Inicio</th><th>Horas</th><th>Partes</th><th>Mecánicos</th><th>Costos</th></tr></thead>
        <tbody>
            @foreach($repairs as $r)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $r->vehicle->identifier }}</td>
                    <td>{{ $r->started_at?->format('Y-m-d H:i') }}</td>
                    <td>{{ $r->duration_hours }}</td>
                    <td>
                        @foreach($r->parts as $p)
                            <div>{{ $p->name }} × {{ $p->pivot->quantity }} — ${{ number_format($p->unit_cost*$p->pivot->quantity,2) }}</div>
                        @endforeach
                    </td>
                    <td>
                        @foreach($r->mechanics as $m)
                            <div>{{ $m->name }} — {{ $m->pivot->hours }} h</div>
                        @endforeach
                    </td>
                    <td>
                        <div>Refacciones: ${{ number_format($r->partsCost(),2) }}</div>
                        <div>Mano de obra: ${{ number_format($r->laborCost(),2) }}</div>
                        <strong>Total: ${{ number_format($r->totalCost(),2) }}</strong>
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <div style="margin-top:12px;">{{ $repairs->links() }}</div>
</div>
@endsection
