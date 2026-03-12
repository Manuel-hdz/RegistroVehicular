@extends('layouts.app')

@section('content')
<div class="card">
    <h2 style="margin-top:0">Recursos Humanos</h2>
    <p style="margin:8px 0 0; color:#4b5563;">
        Bienvenido al modulo de RH. Desde aqui puedes administrar el personal y revisar el Kardex de asistencias.
    </p>
</div>

<div class="card">
    <div class="grid grid-3">
        <div class="card" style="margin:0;">
            <h3 style="margin:0 0 8px;">Personal</h3>
            <p style="margin:0 0 14px; color:#4b5563;">
                Altas, edicion y consulta de datos del personal.
            </p>
            <div class="row">
                <a href="{{ route('personnel.index') }}" class="btn btn-primary">Ir a personal</a>
                <a href="{{ route('personnel.create') }}" class="btn btn-secondary">Alta de personal</a>
            </div>
        </div>

        <div class="card" style="margin:0;">
            <h3 style="margin:0 0 8px;">Kardex</h3>
            <p style="margin:0 0 14px; color:#4b5563;">
                Control de asistencias por fecha y claves administrativas.
            </p>
            <div class="row">
                <a href="{{ route('cardex.index') }}" class="btn btn-primary">Abrir kardex</a>
            </div>
        </div>

        <div class="card" style="margin:0;">
            <h3 style="margin:0 0 8px;">Resumen</h3>
            <p style="margin:0 0 6px;"><strong>Total personal:</strong> {{ $totalPersonnel }}</p>
            <p style="margin:0;"><strong>Activos:</strong> {{ $activePersonnel }}</p>
        </div>
    </div>
</div>
@endsection
