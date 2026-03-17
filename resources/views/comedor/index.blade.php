@extends('layouts.app')

@push('head')
<style>
    .comedor-shell {
        --comedor-paper: #fdf5ec;
        --comedor-card: rgba(255, 251, 246, .88);
        --comedor-line: rgba(96, 58, 35, .12);
        --comedor-red: #d83a31;
        --comedor-red-deep: #9f1713;
        --comedor-red-soft: rgba(216, 58, 49, .16);
        --comedor-olive: #304838;
        --comedor-olive-soft: #edf5ef;
        --comedor-muted: #6c5b53;
        position: relative;
        margin: 8px auto 24px;
        border-radius: 32px;
        overflow: hidden;
        border: 1px solid var(--comedor-line);
        background:
            radial-gradient(circle at top left, rgba(216, 58, 49, .13), transparent 24%),
            radial-gradient(circle at top right, rgba(48, 72, 56, .12), transparent 22%),
            linear-gradient(180deg, #f6eadb 0%, var(--comedor-paper) 54%, #efe1cf 100%);
        box-shadow: 0 26px 70px rgba(76, 48, 29, .12);
    }

    .comedor-header {
        display: flex;
        justify-content: space-between;
        gap: 18px;
        align-items: flex-start;
        padding: 24px 24px 12px;
        flex-wrap: wrap;
    }

    .comedor-brand {
        max-width: 640px;
    }

    .comedor-kicker {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 8px 14px;
        border-radius: 999px;
        background: rgba(48, 72, 56, .08);
        color: var(--comedor-olive);
        font-size: .82rem;
        font-weight: 800;
        letter-spacing: .04em;
        text-transform: uppercase;
    }

    .comedor-title {
        margin: 16px 0 8px;
        font-size: clamp(2rem, 5vw, 4.2rem);
        line-height: .95;
        color: #2f2020;
    }

    .comedor-copy {
        margin: 0;
        max-width: 44ch;
        color: var(--comedor-muted);
        font-size: 1.05rem;
    }

    .comedor-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
    }

    .comedor-pill {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        padding: 11px 16px;
        border-radius: 999px;
        border: 1px solid var(--comedor-line);
        background: rgba(255,255,255,.84);
        color: #2f2020;
        font-weight: 700;
        text-decoration: none;
    }

    .comedor-pill-primary {
        border-color: transparent;
        color: #fff7f0;
        background: linear-gradient(180deg, var(--comedor-red), var(--comedor-red-deep));
        box-shadow: 0 14px 26px rgba(159, 23, 19, .22);
    }

    .comedor-tabs {
        display: flex;
        gap: 12px;
        flex-wrap: wrap;
        padding: 0 24px 24px;
    }

    .comedor-tab {
        padding: 12px 18px;
        border-radius: 999px;
        border: 1px solid rgba(96, 58, 35, .12);
        background: rgba(255,255,255,.72);
        color: var(--comedor-muted);
        font-weight: 800;
        text-decoration: none;
        transition: all .18s ease;
    }

    .comedor-tab.active {
        border-color: transparent;
        color: #fff7ef;
        background: linear-gradient(180deg, var(--comedor-red), var(--comedor-red-deep));
        box-shadow: 0 16px 28px rgba(159, 23, 19, .18);
    }

    .comedor-body {
        display: grid;
        grid-template-columns: minmax(0, 1.05fr) minmax(320px, .95fr);
        gap: 18px;
        padding: 0 24px 24px;
        align-items: stretch;
        min-height: 68vh;
    }

    .comedor-meta {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 12px;
        margin-top: 24px;
    }

    .comedor-stat {
        padding: 16px;
        border-radius: 22px;
        background: rgba(255,255,255,.8);
        border: 1px solid var(--comedor-line);
    }

    .comedor-stat strong {
        display: block;
        font-size: 1.45rem;
        line-height: 1;
    }

    .comedor-stat span {
        display: block;
        margin-top: 8px;
        color: var(--comedor-muted);
        font-size: .92rem;
    }

    .alarm-stage {
        position: relative;
        display: grid;
        place-items: center;
        min-height: 100%;
        border-radius: 28px;
        background: linear-gradient(180deg, rgba(255,255,255,.38), rgba(255,255,255,.14));
        border: 1px solid rgba(255,255,255,.4);
        overflow: hidden;
    }

    .alarm-stage::before,
    .alarm-stage::after,
    .alarm-rings {
        content: "";
        position: absolute;
        width: 260px;
        height: 260px;
        border-radius: 50%;
        border: 1px solid rgba(216, 58, 49, .16);
        inset: 50% auto auto 50%;
        transform: translate(-50%, -50%);
    }

    .alarm-rings {
        animation: comedor-ring 2.8s ease-out infinite;
    }

    .alarm-stage::before {
        animation: comedor-ring 2.8s ease-out .5s infinite;
    }

    .alarm-stage::after {
        animation: comedor-ring 2.8s ease-out 1s infinite;
    }

    .alarm-button {
        position: relative;
        z-index: 1;
        width: min(280px, 72vw);
        aspect-ratio: 1;
        border: none;
        border-radius: 50%;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        gap: 10px;
        padding: 24px;
        text-align: center;
        cursor: pointer;
        color: #fff8f0;
        background:
            radial-gradient(circle at 32% 28%, #ff8a7e 0%, var(--comedor-red) 32%, var(--comedor-red-deep) 76%, #76100d 100%);
        box-shadow:
            0 16px 0 #74100d,
            0 36px 72px rgba(216, 58, 49, .38),
            inset 0 14px 26px rgba(255,255,255,.2),
            inset 0 -18px 26px rgba(0,0,0,.26);
        animation: comedor-alarm 1.15s ease-in-out infinite;
        transition: transform .16s ease, box-shadow .16s ease, filter .16s ease;
    }

    .alarm-button:hover {
        transform: translateY(-4px) scale(1.02);
        filter: saturate(1.04);
    }

    .alarm-button:active {
        transform: translateY(8px) scale(.98);
        box-shadow:
            0 8px 0 #74100d,
            0 18px 34px rgba(216, 58, 49, .34),
            inset 0 14px 26px rgba(255,255,255,.2),
            inset 0 -18px 26px rgba(0,0,0,.26);
    }

    .alarm-icon {
        font-size: 3rem;
        line-height: 1;
        font-weight: 900;
    }

    .alarm-button strong {
        font-size: 1.6rem;
        letter-spacing: .04em;
    }

    .alarm-button span {
        font-size: .96rem;
        max-width: 18ch;
        opacity: .92;
    }

    .records-shell {
        padding: 0 24px 24px;
    }

    .records-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 12px;
        margin-bottom: 16px;
    }

    .records-table {
        border-radius: 26px;
        overflow: hidden;
        border: 1px solid var(--comedor-line);
        background: rgba(255,255,255,.82);
    }

    .records-table table {
        width: 100%;
        border-collapse: collapse;
        margin: 0;
    }

    .records-table th,
    .records-table td {
        padding: 14px 16px;
        border-bottom: 1px solid rgba(96, 58, 35, .08);
        font-size: 16px;
    }

    .records-table th {
        color: var(--comedor-muted);
        font-size: .82rem;
        text-transform: uppercase;
        letter-spacing: .05em;
        background: rgba(237, 245, 239, .72);
    }

    .records-table tbody tr:nth-child(odd) {
        background: rgba(255,255,255,.55);
    }

    .records-table tbody tr:hover {
        background: rgba(237, 245, 239, .92);
    }

    .records-pagination {
        display: flex;
        justify-content: space-between;
        gap: 10px;
        align-items: center;
        flex-wrap: wrap;
        padding: 16px;
    }

    .records-pagination a,
    .records-pagination span {
        border-radius: 999px;
        padding: 10px 14px;
        font-weight: 700;
        text-decoration: none;
    }

    .records-pagination a {
        background: var(--comedor-olive-soft);
        color: var(--comedor-olive);
    }

    .records-pagination .disabled {
        background: #f1f1f1;
        color: #9ca3af;
        pointer-events: none;
    }

    .comedor-modal {
        position: fixed;
        inset: 0;
        display: none;
        align-items: center;
        justify-content: center;
        padding: 18px;
        background: rgba(20, 14, 13, .56);
        backdrop-filter: blur(8px);
        z-index: 1085;
    }

    .comedor-modal.is-open {
        display: flex;
    }

    .comedor-modal-card {
        width: min(460px, 100%);
        overflow: hidden;
        border-radius: 28px;
        border: 1px solid var(--comedor-line);
        background: #fffaf5;
        box-shadow: 0 32px 82px rgba(20, 14, 13, .28);
    }

    .comedor-modal-head {
        padding: 22px 22px 14px;
        background: linear-gradient(180deg, var(--comedor-red), var(--comedor-red-deep));
        color: #fff8f0;
    }

    .comedor-modal-head h3 {
        margin: 0;
        font-size: 1.45rem;
    }

    .comedor-modal-head p {
        margin: 8px 0 0;
        opacity: .92;
    }

    .comedor-modal-body {
        padding: 22px;
    }

    .comedor-modal-body label {
        font-weight: 700;
        margin-bottom: 8px;
    }

    .comedor-modal-body input {
        border-radius: 18px;
        border: 1px solid rgba(96, 58, 35, .16);
        padding: 15px 16px;
        font-size: 1rem;
    }

    .comedor-modal-body input:focus {
        outline: none;
        border-color: rgba(216, 58, 49, .46);
        box-shadow: 0 0 0 4px rgba(216, 58, 49, .12);
    }

    .comedor-timebox {
        margin-top: 12px;
        padding: 14px 16px;
        border-radius: 18px;
        background: rgba(216, 58, 49, .06);
        color: var(--comedor-muted);
        font-size: .94rem;
        font-weight: 700;
    }

    .comedor-modal-actions {
        display: flex;
        gap: 10px;
        flex-wrap: wrap;
        margin-top: 18px;
    }

    .comedor-modal-actions button {
        flex: 1 1 180px;
        border: none;
        border-radius: 999px;
        padding: 13px 18px;
        font-size: 1rem;
        font-weight: 800;
    }

    .comedor-confirm {
        color: #fff7ef;
        background: linear-gradient(180deg, var(--comedor-red), var(--comedor-red-deep));
        box-shadow: 0 16px 26px rgba(159, 23, 19, .18);
    }

    .comedor-cancel {
        color: var(--comedor-olive);
        background: #ead7c1;
    }

    @keyframes comedor-alarm {
        0%, 100% { transform: scale(1); }
        18% { transform: scale(1.02); }
        34% { transform: scale(.985); }
        52% { transform: scale(1.018); }
    }

    @keyframes comedor-ring {
        0% {
            opacity: .5;
            transform: translate(-50%, -50%) scale(.78);
        }
        72% {
            opacity: 0;
            transform: translate(-50%, -50%) scale(1.28);
        }
        100% {
            opacity: 0;
            transform: translate(-50%, -50%) scale(1.28);
        }
    }

    @media (max-width: 991.98px) {
        .comedor-body {
            grid-template-columns: 1fr;
            min-height: auto;
        }

        .comedor-meta,
        .records-grid {
            grid-template-columns: 1fr;
        }

        .alarm-stage {
            min-height: 420px;
        }
    }

    @media (max-width: 576px) {
        .comedor-header,
        .comedor-tabs,
        .comedor-body,
        .records-shell {
            padding-left: 16px;
            padding-right: 16px;
        }

        .comedor-header {
            padding-top: 18px;
        }

        .alarm-stage::before,
        .alarm-stage::after,
        .alarm-rings {
            width: 220px;
            height: 220px;
        }

        .alarm-button {
            width: min(232px, 74vw);
        }

        .alarm-icon {
            font-size: 2.35rem;
        }

        .alarm-button strong {
            font-size: 1.35rem;
        }
    }
</style>
@endpush

@section('content')
@php
    $canViewRecords = auth()->check() && in_array(auth()->user()->role, ['admin', 'superadmin'], true);
    $displayTz = 'America/Mexico_City';
@endphp

<section class="comedor-shell">
    <div class="comedor-header">
        <div class="comedor-brand">
            <span class="comedor-kicker"><i class="bi bi-cup-hot-fill"></i> Registro libre</span>
            <h1 class="comedor-title">Comedor</h1>
            <p class="comedor-copy">
                Presiona el botón rojo, escribe tu nombre y el sistema guardará automáticamente tu nombre, la fecha y la hora.
            </p>
        </div>

        <div class="comedor-actions">
            @auth
                <span class="comedor-pill"><i class="bi bi-person-badge"></i>{{ trim(auth()->user()->name ?? '') !== '' ? auth()->user()->name : auth()->user()->username }}</span>
            @else
                <a class="comedor-pill comedor-pill-primary" href="{{ route('login') }}"><i class="bi bi-box-arrow-in-right"></i>Iniciar sesión</a>
            @endauth
        </div>
    </div>

    <nav class="comedor-tabs" aria-label="Secciones del comedor">
        <a class="comedor-tab {{ $activeTab === 'registro' ? 'active' : '' }}" href="{{ route('comedor.index') }}">Registro</a>
        @if($canViewRecords)
            <a class="comedor-tab {{ $activeTab === 'registros' ? 'active' : '' }}" href="{{ route('comedor.records') }}">Registros</a>
        @endif
    </nav>

    @if($activeTab === 'registros' && $records)
        <div class="records-shell">
            <div class="records-grid">
                <article class="comedor-stat">
                    <strong>{{ $todayCount }}</strong>
                    <span>Registros hoy</span>
                </article>
                <article class="comedor-stat">
                    <strong>{{ $totalCount }}</strong>
                    <span>Total acumulado</span>
                </article>
                <article class="comedor-stat">
                    <strong>{{ $lastRecord ? $lastRecord->recorded_at->timezone($displayTz)->format('H:i:s') : '--:--:--' }}</strong>
                    <span>Último registro</span>
                </article>
            </div>

            <div class="records-table">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Nombre</th>
                            <th>Fecha</th>
                            <th>Hora</th>
                            <th>Capturado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($records as $record)
                            <tr>
                                <td>{{ $record->id }}</td>
                                <td>{{ $record->name }}</td>
                                <td>{{ $record->recorded_at->timezone($displayTz)->format('d/m/Y') }}</td>
                                <td>{{ $record->recorded_at->timezone($displayTz)->format('H:i:s') }}</td>
                                <td>{{ $record->created_at?->timezone($displayTz)->format('d/m/Y H:i:s') }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5">Todavía no hay registros del comedor.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>

                <div class="records-pagination">
                    <span>Página {{ $records->currentPage() }}</span>
                    <div class="d-flex gap-2 flex-wrap">
                        <a class="{{ $records->previousPageUrl() ? '' : 'disabled' }}" href="{{ $records->previousPageUrl() ?: '#' }}">Anterior</a>
                        <a class="{{ $records->nextPageUrl() ? '' : 'disabled' }}" href="{{ $records->nextPageUrl() ?: '#' }}">Siguiente</a>
                    </div>
                </div>
            </div>
        </div>
    @else
        <div class="comedor-body">
            <div>
                <div class="comedor-meta">
                    <article class="comedor-stat">
                        <strong id="clockDisplay">{{ $currentTime->format('H:i:s') }}</strong>
                        <span>Hora actual</span>
                    </article>
                    <article class="comedor-stat">
                        <strong id="dateDisplay">{{ $currentTime->format('d/m/Y') }}</strong>
                        <span>Fecha actual</span>
                    </article>
                    <article class="comedor-stat">
                        <strong>{{ $lastRecord ? $lastRecord->recorded_at->timezone($displayTz)->format('H:i:s') : '--:--:--' }}</strong>
                        <span>Último registro</span>
                    </article>
                </div>
            </div>

            <div class="alarm-stage">
                <div class="alarm-rings" aria-hidden="true"></div>
                <button class="alarm-button" type="button" id="alarmButton">
                    <div class="alarm-icon">!</div>
                    <strong>Registrar</strong>
                    <span>Haz clic para escribir tu nombre</span>
                </button>
            </div>
        </div>
    @endif
</section>

<div class="comedor-modal" id="comedorModal" aria-hidden="true">
    <div class="comedor-modal-card" role="dialog" aria-modal="true" aria-labelledby="comedorModalTitle">
        <div class="comedor-modal-head">
            <h3 id="comedorModalTitle">Registrar asistencia</h3>
            <p>Escribe tu nombre y el sistema guardará el registro en automático.</p>
        </div>
        <div class="comedor-modal-body">
            <form method="POST" action="{{ route('comedor.store') }}" id="comedorForm">
                @csrf
                <label for="comedorName">Nombre</label>
                <input id="comedorName" type="text" name="name" value="{{ old('name') }}" maxlength="120" autocomplete="name" required>

                <div class="comedor-timebox">
                    <div>Fecha: <strong id="modalDate">{{ $currentTime->format('d/m/Y') }}</strong></div>
                    <div>Hora: <strong id="modalTime">{{ $currentTime->format('H:i:s') }}</strong></div>
                </div>

                <div class="comedor-modal-actions">
                    <button class="comedor-confirm" type="submit">Guardar registro</button>
                    <button class="comedor-cancel" type="button" id="closeComedorModal">Cancelar</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    (function () {
        const timezone = 'America/Mexico_City';
        const modal = document.getElementById('comedorModal');
        const openButton = document.getElementById('alarmButton');
        const closeButton = document.getElementById('closeComedorModal');
        const nameInput = document.getElementById('comedorName');
        const form = document.getElementById('comedorForm');
        const clockDisplay = document.getElementById('clockDisplay');
        const dateDisplay = document.getElementById('dateDisplay');
        const modalDate = document.getElementById('modalDate');
        const modalTime = document.getElementById('modalTime');

        function refreshClock() {
            const now = new Date();
            const date = now.toLocaleDateString('es-MX', {
                day: '2-digit',
                month: '2-digit',
                year: 'numeric',
                timeZone: timezone
            });
            const time = now.toLocaleTimeString('es-MX', {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: false,
                timeZone: timezone
            });

            if (clockDisplay) clockDisplay.textContent = time;
            if (dateDisplay) dateDisplay.textContent = date;
            if (modalDate) modalDate.textContent = date;
            if (modalTime) modalTime.textContent = time;
        }

        function openModal() {
            if (!modal) return;
            modal.classList.add('is-open');
            modal.setAttribute('aria-hidden', 'false');
            if (nameInput) {
                nameInput.focus();
                nameInput.select();
            }
        }

        function closeModal() {
            if (!modal) return;
            modal.classList.remove('is-open');
            modal.setAttribute('aria-hidden', 'true');
        }

        refreshClock();
        window.setInterval(refreshClock, 1000);

        if (openButton) openButton.addEventListener('click', openModal);
        if (closeButton) closeButton.addEventListener('click', closeModal);

        if (modal) {
            modal.addEventListener('click', function (event) {
                if (event.target === modal) {
                    closeModal();
                }
            });
        }

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape') {
                closeModal();
            }
        });

        if (form) {
            form.addEventListener('submit', function () {
                const submitButton = form.querySelector('.comedor-confirm');
                if (!submitButton) return;
                submitButton.disabled = true;
                submitButton.textContent = 'Guardando...';
            });
        }

        @if ($errors->any() && request()->routeIs('comedor.index'))
            openModal();
        @endif
    })();
</script>
@endpush
