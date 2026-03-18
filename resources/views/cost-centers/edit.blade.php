@extends('layouts.app')

@section('content')
<div class="card">
    <h2 style="margin-top:0">Editar centro de costos</h2>
    <form method="POST" action="{{ route('cost-centers.update', $costCenter) }}" class="grid grid-3">
        @csrf
        @method('PUT')
        <div>
            <label>Codigo</label>
            <input name="code" value="{{ old('code', $costCenter->code) }}" required>
        </div>
        <div style="grid-column: span 2;">
            <label>Nombre</label>
            <input name="name" value="{{ old('name', $costCenter->name) }}" required>
        </div>
        <div>
            <label><input type="checkbox" name="active" value="1" {{ old('active', $costCenter->active) ? 'checked' : '' }}> Activo</label>
        </div>
        <div style="grid-column:1/-1;" class="row">
            <a class="btn btn-secondary" href="{{ route('cost-centers.index') }}">Cancelar</a>
            <button class="btn btn-primary" type="submit">Actualizar</button>
        </div>
    </form>
</div>
@endsection
