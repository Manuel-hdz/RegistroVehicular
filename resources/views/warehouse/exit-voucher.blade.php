@extends('layouts.app')

@section('content')
<div class="card" style="max-width:860px; margin:0 auto;">
    <div style="display:flex; justify-content:space-between; gap:16px; align-items:flex-start; flex-wrap:wrap; border-bottom:1px solid rgba(16, 52, 37, .12); padding-bottom:18px; margin-bottom:18px;">
        <div>
            <h2 style="margin:0;">Vale de salida</h2>
            <p style="margin:6px 0 0; color:#6d8178; font-weight:700;">{{ $exit->voucher_number }}</p>
        </div>
        <button class="btn btn-primary no-print" type="button" onclick="window.print()">
            <i class="bi bi-printer"></i> Imprimir vale
        </button>
    </div>

    <div class="grid grid-2" style="gap:14px;">
        <div>
            <small style="color:#6d8178; font-weight:800;">Clave del material</small>
            <div style="font-size:1.15rem; font-weight:800;">{{ $exit->part?->clave ?? 'Sin clave' }}</div>
        </div>
        <div>
            <small style="color:#6d8178; font-weight:800;">Centro de costos</small>
            <div style="font-size:1.15rem; font-weight:800;">{{ $exit->costCenter?->name ?? 'Sin centro de costos' }}</div>
        </div>
        <div>
            <small style="color:#6d8178; font-weight:800;">Material</small>
            <div style="font-size:1.15rem; font-weight:800;">{{ $exit->part?->name ?? 'Material sin nombre' }}</div>
        </div>
        <div>
            <small style="color:#6d8178; font-weight:800;">Cantidad</small>
            <div style="font-size:1.15rem; font-weight:800;">{{ number_format((float) $exit->quantity, 2) }}</div>
        </div>
        <div>
            <small style="color:#6d8178; font-weight:800;">Quién despachó</small>
            <div style="font-size:1.15rem; font-weight:800;">{{ $exit->dispatched_by ?? $exit->source }}</div>
        </div>
        <div>
            <small style="color:#6d8178; font-weight:800;">Quién se lo lleva</small>
            <div style="font-size:1.15rem; font-weight:800;">{{ $exit->carried_by }}</div>
        </div>
        <div>
            <small style="color:#6d8178; font-weight:800;">Destino</small>
            <div style="font-size:1.15rem; font-weight:800;">{{ $exit->destination }}</div>
        </div>
        <div>
            <small style="color:#6d8178; font-weight:800;">Fecha y hora de salida</small>
            <div style="font-size:1.15rem; font-weight:800;">{{ $exit->exit_date?->format('Y-m-d H:i') }}</div>
        </div>
        <div>
            <small style="color:#6d8178; font-weight:800;">Responsable</small>
            <div style="font-size:1.15rem; font-weight:800;">{{ $exit->responsible }}</div>
        </div>
    </div>

    <div style="display:grid; grid-template-columns:repeat(2, minmax(0, 1fr)); gap:28px; margin-top:58px;">
        <div style="border-top:1px solid #203129; padding-top:8px; text-align:center; font-weight:800;">Entrega</div>
        <div style="border-top:1px solid #203129; padding-top:8px; text-align:center; font-weight:800;">Recibe</div>
    </div>

    <div class="no-print" style="margin-top:24px; display:flex; gap:10px; flex-wrap:wrap;">
        <a class="btn btn-secondary" href="{{ route('warehouse.movements', ['cost_center_id' => $exit->cost_center_id]) }}">Volver a entradas y salidas</a>
    </div>
</div>

<style>
@media print {
    .app-navbar,
    .app-footer,
    .no-print {
        display: none !important;
    }
    body::before,
    body::after {
        display: none !important;
    }
    main,
    .card {
        margin: 0 !important;
        box-shadow: none !important;
        border: 0 !important;
    }
}
</style>
@if(session('status') && str_starts_with(session('status'), 'Salida registrada'))
<script>
(function(){
    var played = false;
    var audio = new Audio(@json(asset('audio/salida-registrada.mpeg')));
    audio.preload = 'auto';

    function playExitSound(){
        if(played) return;
        audio.currentTime = 0;
        var playback = audio.play();
        if(playback){
            playback.then(function(){ played = true; }).catch(function(){
                document.addEventListener('click', playExitSound, { once:true });
                document.addEventListener('keydown', playExitSound, { once:true });
            });
        }
    }

    playExitSound();
})();
</script>
@endif
@endsection


