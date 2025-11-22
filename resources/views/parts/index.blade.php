@extends('layouts.app')

@section('content')
<div class="card">
    <div class="row" style="justify-content: space-between;">
        <h2 style="margin:0">Refacciones</h2>
        <a href="{{ route('parts.create') }}" class="btn btn-primary">Nueva Refacción</a>
    </div>
</div>

<div class="card">
    <table>
        <thead><tr><th>#</th><th>Nombre</th><th>Costo</th><th>Activa</th><th></th></tr></thead>
        <tbody>
            @foreach($parts as $p)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $p->name }}</td>
                    <td>${{ number_format($p->unit_cost, 2) }}</td>
                    <td>{{ $p->active ? 'Sí' : 'No' }}</td>
                    <td><a class="btn btn-secondary" href="{{ route('parts.edit', $p) }}">Editar</a></td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <div style="margin-top:12px;">{{ $parts->links() }}</div>
</div>
@endsection
