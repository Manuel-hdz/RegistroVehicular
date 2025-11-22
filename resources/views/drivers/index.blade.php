@extends('layouts.app')

@section('content')
<div class="card">
    <div class="row" style="justify-content: space-between;">
        <h2 style="margin:0">Conductores</h2>
        <a href="{{ route('drivers.create') }}" class="btn btn-primary">Nuevo Conductor</a>
    </div>
</div>

@push('head-pre')
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">
@endpush
@push('scripts')
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script>
        $(function(){
            $('#driversTable').DataTable({
                pageLength: 25,
                order: [[0,'asc']],
                language: { url: 'https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json' }
            });
        });
    </script>
@endpush

<div class="card">
    <table id="driversTable">
        <thead>
            <tr>
                <th>#</th>
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
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $d->name }}</td>
                    <td>{{ $d->employee_number }}</td>
                    <td>{{ $d->license }}</td>
                    <td>{{ $d->active ? 'Sí' : 'No' }}</td>
                    <td>
                        <a class="btn btn-secondary" href="{{ route('drivers.edit', $d) }}" title="Editar" aria-label="Editar">
                            <i class="bi bi-pencil-square" aria-hidden="true"></i>
                        </a>
                        @auth
                            @if(auth()->user()->role === 'superadmin')
                                <form action="{{ route('drivers.destroy', $d) }}" method="POST" style="display:inline;" onsubmit="return confirm('¿Eliminar conductor?');">
                                    @csrf
                                    @method('DELETE')
                                    <button class="btn btn-secondary" type="submit" title="Eliminar" aria-label="Eliminar">
                                        <i class="bi bi-trash" aria-hidden="true" style="color:#dc2626;"></i>
                                    </button>
                                </form>
                            @endif
                        @endauth
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endsection
@push('head-pre')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
@endpush
