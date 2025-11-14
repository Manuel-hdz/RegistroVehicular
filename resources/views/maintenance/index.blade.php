@extends('layouts.app')

@section('content')
<div class="card">
    <div class="row" style="justify-content: space-between;">
        <h2 style="margin:0">Estado de Unidades</h2>
    </div>
</div>

<div class="card">
    <div class="grid grid-2">
    @foreach($vehicles as $v)
        @php
            $color = $v->availability === 'available' ? '#16a34a' : '#dc2626';
            $title = $v->identifier ?: ($v->model ? $v->model.' '.$v->year : 'Unidad');
        @endphp
        <div class="card">
            <div class="row" style="align-items:center; gap:12px;">
                <button type="button" class="maint-icon btn btn-icon" style="color: {{ $color }}; border-color: transparent; background:transparent; padding:6px;"
                    title="Cambiar estado" aria-label="Cambiar estado"
                    data-update-url="{{ route('maintenance.update', $v) }}"
                    data-availability="{{ $v->availability }}"
                    data-note="{{ $v->maintenance_note }}"
                    data-title="{{ $title }}">
                    @switch($v->vtype)
                        @case('camion')
                            <svg width="64" height="64" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M3 7h10v7h2.5a2.5 2.5 0 0 1 2.45 2h.55a2.5 2.5 0 1 1 0 1H18a2.5 2.5 0 0 1-4.9 0H8.9a2.5 2.5 0 0 1-4.9 0H3V8a1 1 0 0 1 0-1Zm11 1H4v6h10V8Zm1 6h2.5a1.5 1.5 0  0 1 1.5 1.5V16h-5v-.5A1.5 1.5 0 0 1 15 14Z"/></svg>
                            @break
                        @case('pickup')
                            <svg width="64" height="64" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M3 13v-2a2 2 0 0 1 2-2h4l2 3h6a2 2 0 0 1 2 2v2h-1a2.5 2.5 0 1 1-5 0H9a2.5 2.5 0 1 1-5 0H3v-3Zm3-2a1 1 0 0 0-1 1v1h6l-1.33-2H6Z"/></svg>
                            @break
                        @case('furgoneta')
                            <svg width="64" height="64" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M2 12h11V7h4l3 4v5h-1a2.5 2.5 0 1 1-5 0H9a2.5 2.5 0 1 1-5 0H2v-4Zm17 0h2l-2-3h-2v3Z"/></svg>
                            @break
                        @default
                            <svg width="64" height="64" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true"><path d="M5 11l2.5-4.5A2 2 0 0 1 9.2 5h5.6a2 2 0 0 1 1.7 1L19 11h1a1 1 0 0 1 1 1v3h-1.05a2.5 2.5 0 1 1-4.9 0H9.95a2.5 2.5 0 1 1-4.9 0H4v-3a1 1 0 0 1 1-1h0Zm2.3 0h9.4l-1.8-3.24a1 1 0 0 0-.87-.5H9.2a1 1 0 0 0-.87.5L7.3 11Z"/></svg>
                    @endswitch
                </button>
                <div>
                    <strong>{{ $title }}</strong>
                    <div style="font-size:14px; color:#555; margin-top:4px;">Estado: {{ $v->availability === 'available' ? 'Disponible' : 'No disponible' }}</div>
                    @if($v->maintenance_note)
                        <div style="font-size:14px; color:#9a3412; margin-top:6px;">{{ $v->maintenance_note }}</div>
                    @endif
                    <div style="margin-top:6px;">
                        <a class="btn btn-link" href="{{ route('repairs.index', ['vehicle_id' => $v->id]) }}">Historial de reparaciones</a>
                    </div>
                </div>
            </div>
        </div>
    @endforeach
    </div>
</div>
<div class="backdrop" id="maintBackdrop" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="maintTitle">
    <div class="modal" role="document">
        <header>
            <strong id="maintTitle">Actualizar estado</strong>
            <button class="close-x" type="button" aria-label="Cerrar" id="btnCloseMaint">×</button>
        </header>
        <div class="content">
            <form id="maintForm" method="POST" action="#" class="grid" style="gap:10px;">
                @csrf
                @method('PUT')
                <div>
                    <label>Estado</label>
                    <select name="availability" id="maintAvailability" required>
                        <option value="available">Disponible</option>
                        <option value="unavailable">No disponible</option>
                    </select>
                </div>
                <div>
                    <label>Descripción (opcional)</label>
                    <textarea name="maintenance_note" id="maintNote"></textarea>
                </div>
                <div class="row" style="justify-content:flex-end; gap:8px;">
                    <button class="btn btn-secondary" type="button" id="btnCancelMaint">Cancelar</button>
                    <button class="btn btn-primary" type="submit">Guardar</button>
                </div>
            </form>
        </div>
    </div>
    </div>
<script>
    (function(){
        const backdrop = document.getElementById('maintBackdrop');
        const closeX = document.getElementById('btnCloseMaint');
        const cancelBtn = document.getElementById('btnCancelMaint');
        const form = document.getElementById('maintForm');
        const sel = document.getElementById('maintAvailability');
        const note = document.getElementById('maintNote');
        const title = document.getElementById('maintTitle');
        function open(){ backdrop.setAttribute('aria-hidden','false'); }
        function close(){ backdrop.setAttribute('aria-hidden','true'); }
        document.querySelectorAll('.maint-icon').forEach(function(btn){
            btn.addEventListener('click', function(){
                form.action = btn.dataset.updateUrl;
                sel.value = btn.dataset.availability || 'available';
                note.value = btn.dataset.note || '';
                title.textContent = 'Actualizar: ' + (btn.dataset.title || 'Unidad');
                open();
            });
        });
        if(closeX) closeX.addEventListener('click', close);
        if(cancelBtn) cancelBtn.addEventListener('click', close);
        if(backdrop) backdrop.addEventListener('click', function(e){ if(e.target === backdrop) close(); });
        document.addEventListener('keydown', function(e){ if(e.key === 'Escape') close(); });
    })();
</script>
@endsection
