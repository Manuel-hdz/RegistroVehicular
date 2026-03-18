@extends('layouts.app')

@section('content')
<div class="card">
    <h2 style="margin-top:0">Editar regla de vacaciones</h2>
    <form method="POST" action="{{ route('vacation-policies.update', $vacationPolicy) }}" class="grid grid-3">
        @csrf
        @method('PUT')
        <div>
            <label>Año cumplido</label>
            <input type="number" name="service_year" min="1" step="1" value="{{ old('service_year', $vacationPolicy->service_year) }}" required>
        </div>
        <div>
            <label>Días a otorgar</label>
            <input type="number" name="vacation_days" min="1" step="1" value="{{ old('vacation_days', $vacationPolicy->vacation_days) }}" required>
        </div>
        <div>
            <label><input type="checkbox" name="active" value="1" {{ old('active', $vacationPolicy->active) ? 'checked' : '' }}> Activa</label>
        </div>
        <div style="grid-column: 1/-1;">
            <label>Notas</label>
            <input name="notes" value="{{ old('notes', $vacationPolicy->notes) }}" placeholder="Ejemplo: aplicar también a los años 6 al 10">
        </div>
        <div style="grid-column:1/-1;" class="row">
            <a class="btn btn-secondary" href="{{ route('vacation-policies.index') }}">Cancelar</a>
            <button class="btn btn-primary" type="submit">Actualizar</button>
        </div>
    </form>
</div>
@endsection
