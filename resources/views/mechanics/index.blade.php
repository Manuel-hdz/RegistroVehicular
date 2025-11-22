@extends('layouts.app')

@section('content')
<div class="card">
    <div class="row" style="justify-content: space-between;">
        <h2 style="margin:0">Mecánicos</h2>
        <a href="{{ route('mechanics.create') }}" class="btn btn-primary">Nuevo Mecánico</a>
    </div>
</div>

<div class="card">
    <table>
        <thead><tr><th>#</th><th>Nombre</th><th>Salario diario</th><th>Activo</th><th></th></tr></thead>
        <tbody>
            @foreach($mechanics as $m)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $m->name }}</td>
                    <td>${{ number_format($m->daily_salary, 2) }}</td>
                    <td>{{ $m->active ? 'Sí' : 'No' }}</td>
                    <td><a class="btn btn-secondary" href="{{ route('mechanics.edit', $m) }}">Editar</a></td>
                </tr>
            @endforeach
        </tbody>
    </table>
    <div style="margin-top:12px;">{{ $mechanics->links() }}</div>
</div>
@endsection
