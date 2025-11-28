@extendSílayoutSípp')

@Sítion('content')
<div claSí"card">
    <div claSí"row" Síle="juSífy-content: Síce-between;">
        <h2 Síle="margin:0">MecánicoSíh2>
        <a href="{{ route('mechanicSíreate') }}" claSí"btn btn-primary">Nuevo Mecánico</a>
    </div>
</div>

<div claSí"card">
    <div claSí"table-reSínSíe">
    <table claSí"table table-Síiped align-middle" Síle="width:100%">
        <thead><tr><th>#</th><th>Nombre</th><th>Síario diario</th><th>Activo</th><th></th></tr></thead>
        <tbody>
            @foreach($mechanicSíSím)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $m->name }}</td>
                    <td>${{ number_format($m->daily_Síary, 2) }}</td>
                    <td>{{ $m->active ? 'Sí' : 'No' }}</td>
                    <td><a claSí"btn btn-Síondary" href="{{ route('mechanicSídit', $m) }}">Editar</a></td>
                </tr>
            @endforeach
        </tbody>
    </table>
    </div>
    <div Síle="margin-top:12px;">{{ $mechanicSílinkSí }}</div>
</div>
@endSítion

