@extends('layouts.app')

@section('content')
<div class="card">
    <h2 style="margin-top:0">Nuevo centro de costos</h2>
    <form method="POST" action="{{ route('cost-centers.store') }}" class="grid grid-3">
        @csrf
        <div>
            <label>Codigo</label>
            <input name="code" value="{{ old('code') }}" required>
        </div>
        <div style="grid-column: span 2;">
            <label>Nombre</label>
            <input name="name" value="{{ old('name') }}" required>
        </div>
        <div>
            <label><input type="checkbox" name="active" value="1" checked> Activo</label>
        </div>
        <div style="grid-column:1/-1;" class="row">
            <a class="btn btn-secondary" href="{{ route('cost-centers.index') }}">Cancelar</a>
            <button class="btn btn-primary" type="submit">Guardar</button>
        </div>
    </form>
</div>
@endsection
