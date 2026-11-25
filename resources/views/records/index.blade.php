@extends('layouts.app')

@section('content')
<div class="card">
    <h2>Eliminar registros</h2>
    <p>Permiso especial exclusivo del superadmin. Selecciona una tabla y revisa el registro que deseas eliminar.</p>
    <p style="color:#b91c1c;">La eliminación es permanente. Los datos relacionados configurados con eliminación en cascada también se eliminarán.</p>
    <form method="GET" action="{{ route('records.index') }}" style="display:flex; gap:12px; flex-wrap:wrap; align-items:end;">
        <div>
            <label for="record-table">Tabla</label>
            <select id="record-table" name="table" onchange="this.form.submit()">
                @foreach($tables as $tableName)
                    <option value="{{ $tableName }}" @selected($table === $tableName)>{{ $tableName }}</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="record-search">Buscar registro</label>
            <input id="record-search" type="search" name="search" value="{{ $search }}" maxlength="150" placeholder="ID, nombre u otro dato visible">
        </div>
        <button class="btn btn-primary" type="submit">Buscar</button>
    </form>
</div>
<div class="card">
    @if($table && $keyColumns === [])
        <p>Esta tabla no tiene una clave única para identificar un registro individual.</p>
    @endif
    <div class="table-responsive">
        <table class="table table-striped align-middle">
            <thead><tr>
                @foreach($columns as $column)<th>{{ $column }}</th>@endforeach
                <th>Acción</th>
            </tr></thead>
            <tbody>
                @forelse($records ?? [] as $record)
                    <tr>
                        @foreach($record['values'] as $value)
                            <td style="max-width:320px; overflow-wrap:anywhere;">{{ $value === null ? '—' : \Illuminate\Support\Str::limit((string) $value, 250) }}</td>
                        @endforeach
                        <td>
                            @if($record['token'])
                                <form method="POST" action="{{ route('records.destroy', ['table' => $table]) }}" onsubmit="return confirm('¿Eliminar permanentemente este registro y sus relaciones en cascada? Revisa los datos de esta fila antes de continuar.');">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="record" value="{{ $record['token'] }}">
                                    <button type="submit" class="btn btn-danger">Eliminar</button>
                                </form>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="{{ count($columns) + 1 }}">Sin registros.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($records)<div>{{ $records->links('pagination::bootstrap-5') }}</div>@endif
</div>
@endsection
