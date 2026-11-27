@extends('layouts.app')

@section('content')
<div class="card">
    <h2 style="margin:0;">Bitácora general del sistema</h2>
    <p style="margin:6px 0 0;">Altas, cambios, bajas, accesos y logs por usuario. Solo visible para el superadministrador.</p>
    <p class="text-muted" style="margin:6px 0 0;">El historial comienza al activar esta bitácora. Horarios de Ciudad de México. Los datos de autenticación se ocultan.</p>
</div>
<div class="card">
    <form method="GET" action="{{ route('audit-logs.index') }}" class="grid grid-3" style="margin-bottom:18px;">
        <div>
            <label for="audit-actor">Usuario</label>
            <select id="audit-actor" name="actor_id">
                <option value="">Todos</option>
                <option value="0" @selected(request('actor_id') === '0')>Sistema / visitante</option>
                @foreach($actors as $actor)
                    <option value="{{ $actor->actor_id }}" @selected((string)request('actor_id') === (string)$actor->actor_id)>{{ $actor->actor_name }} ({{ $actor->actor_username }})</option>
                @endforeach
            </select>
        </div>
        <div>
            <label for="audit-action">Acción</label>
            <select id="audit-action" name="action">
                <option value="">Todas</option>
                @foreach($actions as $value => $label)<option value="{{ $value }}" @selected(request('action') === $value)>{{ $label }}</option>@endforeach
            </select>
        </div>
        <div>
            <label for="audit-subject">Tipo de registro</label>
            <select id="audit-subject" name="subject_type">
                <option value="">Todos</option>
                @foreach($subjects as $subject)<option value="{{ $subject }}" @selected(request('subject_type') === $subject)>{{ $subject }}</option>@endforeach
            </select>
        </div>
        <div><label for="audit-from">Desde</label><input id="audit-from" type="date" name="date_from" value="{{ request('date_from') }}"></div>
        <div><label for="audit-to">Hasta</label><input id="audit-to" type="date" name="date_to" value="{{ request('date_to') }}"></div>
        <div><label for="audit-search">Buscar</label><input id="audit-search" name="search" maxlength="150" value="{{ request('search') }}" placeholder="Usuario, descripción, registro o ruta"></div>
        <div class="row" style="grid-column:1/-1;">
            <button type="submit" class="btn btn-primary">Consultar</button>
            <a href="{{ route('audit-logs.index') }}" class="btn btn-secondary">Limpiar</a>
        </div>
    </form>
    <p>{{ number_format($logs->total()) }} eventos encontrados.</p>
    <div class="table-responsive">
        <table class="table table-striped align-middle">
            <thead><tr><th>Fecha</th><th>Usuario</th><th>Acción / registro</th><th>Descripción</th><th>Solicitud</th><th>Detalle</th><th>Acciones</th></tr></thead>
            <tbody>
            @forelse($logs as $log)
                <tr>
                    <td style="white-space:nowrap;">{{ $log->created_at->timezone($timezone)->format('d/m/Y H:i:s') }}<br><small>#{{ $log->id }}</small></td>
                    <td>{{ $log->actor_name ?? 'Sistema / visitante' }}<br><small>{{ $log->actor_username }}</small></td>
                    <td>{{ $actions[$log->action] ?? $log->action }}<br><small>{{ $log->subject_type }}{{ $log->subject_id !== null ? ' #'.$log->subject_id : '' }}</small></td>
                    <td style="min-width:180px; white-space:normal; overflow-wrap:anywhere;">{{ $log->description }}</td>
                    <td style="max-width:230px; overflow-wrap:anywhere;">{{ $log->method }} {{ $log->route ?? $log->path ?? 'Proceso interno' }}<br>@if($log->status_code)<small>HTTP {{ $log->status_code }}</small>@endif</td>
                    <td>
                        <details>
                            <summary>Ver detalle</summary>
                            @foreach(['Antes' => $log->before_values, 'Después' => $log->after_values, 'Información' => $log->metadata] as $label => $values)
                                @if(!empty($values))
                                    <strong>{{ $label }}</strong>
                                    <pre style="white-space:pre-wrap; overflow-wrap:anywhere; min-width:240px; max-width:480px; max-height:300px; overflow:auto;">{{ json_encode($values, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) }}</pre>
                                @endif
                            @endforeach
                            <small>IP: {{ $log->ip_address ?? 'Proceso interno' }}<br>Solicitud: {{ $log->request_id ?? 'Sin solicitud web' }}</small>
                        </details>
                    </td>
                    <td>@include('partials.delete-record', ['deletableRecord' => $log])</td>
                </tr>
            @empty
                <tr><td colspan="7">No hay eventos con los filtros seleccionados.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
    {{ $logs->links('pagination::bootstrap-5') }}
</div>
@endsection
