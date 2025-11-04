@extends('layouts.app')

@section('content')
<div class="card">
    <div class="row" style="justify-content: space-between;">
        <h2 style="margin:0">Conductores</h2>
        <a href="{{ route('drivers.create') }}" class="btn btn-primary">Nuevo Conductor</a>
    </div>
</div>

<div class="card">
    <table>
        <thead>
            <tr>
                <th>Nombre</th>
                <th>Número</th>
                <th>Licencia</th>
                <th>Activo</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @foreach($drivers as $d)
                <tr>
                    <td>{{ $d->name }}</td>
                    <td>{{ $d->employee_number }}</td>
                    <td>{{ $d->license }}</td>
                    <td>{{ $d->active ? 'Sí' : 'No' }}</td>
                    <td><a class="btn btn-secondary" href="{{ route('drivers.edit', $d) }}">Editar</a></td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <div style="margin-top:12px;">{{ $drivers->links() }}</div>
</div>
@endsection
