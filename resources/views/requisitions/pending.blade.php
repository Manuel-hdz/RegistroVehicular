@extends('layouts.app')

@php
    $badgeStyles = [
        'pending' => 'background:#fff4cc; color:#8a6500;',
        'reviewing' => 'background:#dbeafe; color:#1d4ed8;',
        'approved' => 'background:#dcfce7; color:#166534;',
        'purchased' => 'background:#ede9fe; color:#6d28d9;',
        'delivered' => 'background:#d1fae5; color:#065f46;',
        'rejected' => 'background:#fee2e2; color:#b91c1c;',
    ];
@endphp

@section('content')
<div class="card">
    <div class="row" style="justify-content: space-between; align-items:center; gap:12px;">
        <div>
            <h2 style="margin:0;">Pendientes</h2>
            <p style="margin:6px 0 0; color:#6b7280;">Solicitudes registradas desde el formulario publico de requisiciones.</p>
        </div>
        <div class="row" style="gap:10px;">
            <a href="{{ route('requisitions.create') }}" class="btn btn-secondary">Abrir formulario</a>
            <form method="GET" action="{{ route('requisitions.pending') }}">
                <select name="status" onchange="this.form.submit()" style="min-width:190px;">
                    <option value="">Todos los estatus</option>
                    @foreach($statuses as $key => $label)
                        <option value="{{ $key }}" @selected($selectedStatus === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </form>
        </div>
    </div>
</div>

@forelse($requisitions as $requisition)
    <div class="card">
        <div class="row" style="justify-content: space-between; align-items:flex-start; gap:12px; margin-bottom:14px;">
            <div>
                <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                    <h3 style="margin:0;">{{ $requisition->folio }}</h3>
                    <span style="padding:6px 10px; border-radius:999px; font-weight:700; {{ $badgeStyles[$requisition->status] ?? 'background:#e5e7eb; color:#374151;' }}">
                        {{ $requisition->status_label }}
                    </span>
                </div>
                <p style="margin:8px 0 0; color:#4b5563;">
                    Solicitante: <strong>{{ $requisition->requester_name }}</strong>
                    · Centro de costos: <strong>{{ $requisition->costCenter?->code }} - {{ $requisition->costCenter?->name }}</strong>
                     · Registrada: {{ optional($requisition->created_at)->format('d/m/Y H:i') }}
                </p>
            </div>
            <div style="display:grid; gap:10px; min-width:240px;">
                <div style="padding:10px 14px; border-radius:12px; background:#f7faf8; border:1px solid #dbe7df; text-align:center;">
                    <div style="font-size:28px; font-weight:800; color:#0f5132; line-height:1;">{{ $requisition->items->count() }}</div>
                    <div style="color:#6b7280;">registros solicitados</div>
                </div>
                <form method="POST" action="{{ route('requisitions.status', $requisition) }}" class="row" style="gap:8px;">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status_context" value="{{ $selectedStatus }}">
                    <select name="status" style="min-width:160px;">
                        @foreach($statuses as $key => $label)
                            <option value="{{ $key }}" @selected($requisition->status === $key)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="btn btn-primary">Guardar estatus</button>
                </form>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-striped align-middle" style="width:100%">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Material</th>
                        <th>Cantidad</th>
                        <th>Equipo destino</th>
                        <th>Justificacion</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($requisition->items as $item)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $item->material_name }}</td>
                            <td>{{ number_format((float) $item->quantity, 2) }}</td>
                            <td>
                                {{ $item->equipmentVehicle?->identifier ?: 'Sin equipo especifico' }}
                                {{ $item->equipmentVehicle?->plate ? ' / '.$item->equipmentVehicle->plate : '' }}
                            </td>
                            <td>{{ $item->justification ?: 'Sin justificacion' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@empty
    <div class="card">
        <h3 style="margin-top:0;">Sin registros</h3>
        <p style="margin-bottom:0; color:#6b7280;">No hay solicitudes para el filtro seleccionado.</p>
    </div>
@endforelse

<div class="card" style="padding-top:12px; padding-bottom:12px;">
    {{ $requisitions->links() }}
</div>
@endsection
