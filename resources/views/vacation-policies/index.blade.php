@extends('layouts.app')

@section('content')
<div class="card">
    <div>
        <h2 style="margin:0">Tabla de vacaciones</h2>
        <p style="margin:8px 0 0; color:#4b5563;">Edita solo los días de los rangos fijos que usa el sistema para acreditar vacaciones por aniversario.</p>
    </div>
</div>

<div class="card">
    <form method="POST" action="{{ route('vacation-policies.update-table') }}">
        @csrf
        @method('PUT')
        <div class="table-responsive">
            <table class="table table-striped align-middle" style="width:100%">
                <thead>
                    <tr>
                        <th>Antiguedad</th>
                        <th>Inicio</th>
                        <th>Días a otorgar</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($vacationPolicies as $vacationPolicy)
                        <tr>
                            <td><strong>{{ \App\Models\VacationPolicy::fixedRanges()[$vacationPolicy->service_year] ?? $vacationPolicy->service_year }}</strong></td>
                            <td>{{ $vacationPolicy->service_year }}</td>
                            <td style="width:180px;">
                                <input
                                    type="number"
                                    min="0"
                                    step="1"
                                    name="vacation_days[{{ $vacationPolicy->service_year }}]"
                                    value="{{ old('vacation_days.' . $vacationPolicy->service_year, $vacationPolicy->vacation_days) }}"
                                    required
                                >
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div style="margin-top:14px; display:flex; justify-content:flex-end;">
            <button class="btn btn-primary" type="submit">Guardar tabla</button>
        </div>
    </form>
    <div style="margin-top:12px; color:#4b5563; font-size:14px;">
        Los años no se pueden agregar ni quitar. El sistema usa estos rangos fijos para calcular el saldo automático al cumplir cada aniversario.
    </div>
</div>
@endsection
