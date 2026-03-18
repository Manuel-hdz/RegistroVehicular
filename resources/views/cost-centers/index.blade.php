@extends('layouts.app')

@section('content')
<div class="card">
    <div class="row" style="justify-content: space-between;">
        <h2 style="margin:0">Centros de costos</h2>
        <a href="{{ route('cost-centers.create') }}" class="btn btn-primary">Nuevo centro</a>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="table table-striped align-middle" style="width:100%">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Codigo</th>
                    <th>Nombre</th>
                    <th>Activo</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($costCenters as $costCenter)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $costCenter->code }}</td>
                        <td>{{ $costCenter->name }}</td>
                        <td>{{ $costCenter->active ? 'Si' : 'No' }}</td>
                        <td><a class="btn btn-secondary" href="{{ route('cost-centers.edit', $costCenter) }}">Editar</a></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    <div style="margin-top:12px;">{{ $costCenters->links() }}</div>
</div>
@endsection
