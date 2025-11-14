@extends('layouts.app')

@section('content')
<div class="card">
    <h2 style="margin-top:0">Nueva Reparación</h2>
    <form method="POST" action="{{ route('repairs.store') }}" class="grid">
        @csrf
        <div class="grid grid-3">
            <div>
                <label>Unidad</label>
                <select name="vehicle_id" required>
                    <option value="">Seleccione…</option>
                    @foreach($vehicles as $v)
                        <option value="{{ $v->id }}" @selected(old('vehicle_id')==$v->id)>{{ ($v->identifier ? $v->identifier.' — ' : '').$v->plate }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label>Inicio</label>
                <input type="datetime-local" name="started_at" value="{{ old('started_at') }}">
            </div>
            <div>
                <label>Duración (horas)</label>
                <input type="number" step="0.25" name="duration_hours" value="{{ old('duration_hours', 1) }}" min="0" required>
            </div>
        </div>

        <div class="grid grid-3">
            <div>
                <label>Refacciones</label>
                <div id="partsRepeater">
                    @for($i=0;$i<3;$i++)
                        <div class="row" style="gap:6px; margin-bottom:6px;">
                            <select name="parts[{{ $i }}][id]">
                                <option value="">—</option>
                                @foreach($parts as $p)
                                    <option value="{{ $p->id }}">{{ $p->name }} (${{ number_format($p->unit_cost,2) }})</option>
                                @endforeach
                            </select>
                            <input type="number" name="parts[{{ $i }}][qty]" min="1" value="1" style="width:100px;">
                        </div>
                    @endfor
                </div>
            </div>
            <div>
                <label>Mecánicos</label>
                <div id="mechsRepeater">
                    @for($i=0;$i<3;$i++)
                        <div class="row" style="gap:6px; margin-bottom:6px;">
                            <select name="mechanics[{{ $i }}][id]">
                                <option value="">—</option>
                                @foreach($mechanics as $m)
                                    <option value="{{ $m->id }}">{{ $m->name }} (${{ number_format($m->daily_salary,2) }}/día)</option>
                                @endforeach
                            </select>
                            <input type="number" step="0.25" name="mechanics[{{ $i }}][hours]" min="0" value="1" style="width:120px;">
                        </div>
                    @endfor
                </div>
            </div>
            <div>
                <label>Notas</label>
                <textarea name="notes">{{ old('notes') }}</textarea>
            </div>
        </div>

        <div class="row actions-stick">
            <a class="btn btn-secondary" href="{{ route('repairs.index') }}">Cancelar</a>
            <button class="btn btn-primary" type="submit">Guardar</button>
        </div>
    </form>
</div>
@endsection

