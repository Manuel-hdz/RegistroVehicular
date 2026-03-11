<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registro Vehicular</title>
    @stack('head-pre')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root{
            --green: #006847;
            --green-700: #005b3e;
            --green-800: #004a33;
            --yellow: #FFCD11;
            --grey-900: #2F2F2F;
            --grey-100: #F3F4F6;
            --grey-300: #E5E7EB;
            --footer-h: 68px;
        }
        * { box-sizing: border-box; }
        html, body { overscroll-behavior-y: contain; }
        body::before { content:""; position: fixed; top:0; left:0; right:0; height: 28px; background: linear-gradient(90deg, var(--green-800), var(--green)); z-index: 1059; pointer-events:none; }
        body { font-family: "Segoe UI", "Helvetica Neue", Arial, sans-serif; margin:0; background:var(--grey-100); color:var(--grey-900); font-size:18px; line-height:1.5; }

        .app-navbar {
            z-index: 1060;
            padding: .6rem .75rem;
            background: linear-gradient(110deg, var(--green-800), var(--green) 58%, #0a7c55);
            border-bottom: 1px solid rgba(255,255,255,.2);
            box-shadow: 0 10px 24px rgba(0, 62, 43, .32);
            backdrop-filter: blur(8px);
            animation: navDrop .45s ease;
        }
        .app-navbar .container-fluid { gap: .75rem; align-items: center; }
        .navbar-brand { margin-right: 0; color: #fff !important; }
        .navbar-brand strong { font-size: 1.02rem; line-height: 1.05; letter-spacing: .2px; }
        .navbar-brand .small { font-size: .72rem; opacity: .86; letter-spacing: .25px; }
        .logo { height:44px; width:auto; display:block; filter: drop-shadow(0 1px 1px rgba(0,0,0,.15)); background:transparent; }

        .home-link {
            display:inline-flex;
            align-items:center;
            justify-content:center;
            width:38px;
            height:38px;
            border-radius:11px;
            background: rgba(255,255,255,.14);
            color:#fff;
            margin-right:2px;
            border:1px solid rgba(255,255,255,.34);
            transition: all .22s ease;
        }
        .home-link:hover {
            background: rgba(255,255,255,.25);
            transform: translateY(-1px);
            color: #fff;
        }

        .app-navbar .navbar-toggler {
            border: 1px solid rgba(255,255,255,.35);
            border-radius: 12px;
            padding: .42rem .62rem;
            background: rgba(255,255,255,.08);
        }
        .app-navbar .navbar-toggler:focus {
            box-shadow: 0 0 0 .2rem rgba(255,205,17,.34);
        }

        .app-navbar-collapse { width: 100%; }
        .navbar-shell {
            display: flex;
            align-items: center;
            width: 100%;
            gap: .7rem;
        }
        .main-nav {
            gap: .45rem;
            align-items: center;
            margin-top: .2rem;
        }
        .main-nav .nav-link {
            color: #e9fff2;
            font-weight: 600;
            font-size: .92rem;
            border-radius: 999px;
            padding: .5rem .84rem;
            border: 1px solid transparent;
            transition: all .2s ease;
            display: inline-flex;
            align-items: center;
            gap: .42rem;
            white-space: nowrap;
        }
        .main-nav .nav-link:hover {
            color: #fff;
            background: rgba(255,255,255,.14);
            border-color: rgba(255,255,255,.26);
        }
        .main-nav .nav-link.active {
            color: #04281b;
            background: linear-gradient(180deg, #ffe38c, var(--yellow));
            border-color: rgba(0,0,0,.1);
            box-shadow: 0 4px 10px rgba(0,0,0,.16);
        }

        .user-pill {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            padding: .46rem .76rem;
            border-radius: 999px;
            background: rgba(0, 0, 0, .2);
            border: 1px solid rgba(255,255,255,.22);
            color: #f8fff9;
            font-size: .85rem;
        }
        .user-pill .bi { opacity: .86; }

        .btn-logout {
            border-radius: 999px;
            border-width: 1px;
            padding-left: .95rem;
            padding-right: .95rem;
        }
        .guest-actions {
            flex-wrap: wrap;
            justify-content: flex-end;
        }
        .guest-actions .btn { border-radius: 999px; }
        .nav-divider {
            width: 100%;
            border-top: 1px solid rgba(255,255,255,.2);
            margin: .3rem 0 .15rem;
        }

        @media (min-width: 992px){
            .main-nav { margin-top: 0; }
            .navbar-shell { justify-content: space-between; }
            .user-nav { margin-left: auto; }
            .navbar-brand strong { font-size: 1.08rem; }
        }
        @media (max-width: 991.98px){
            .app-navbar { padding: .65rem .68rem; }
            .app-navbar-collapse { margin-top: .65rem; }
            .navbar-shell {
                flex-direction: column;
                align-items: stretch;
                background: rgba(0,40,27,.62);
                border: 1px solid rgba(255,255,255,.19);
                border-radius: 14px;
                padding: .58rem;
                box-shadow: inset 0 1px 0 rgba(255,255,255,.08);
            }
            .main-nav .nav-link { width: 100%; border-radius: 10px; }
            .user-nav .nav-item { width: 100%; }
            .user-pill, .btn-logout { width: 100%; justify-content: center; }
            .navbar-brand strong { font-size: .94rem; }
            .navbar-brand .small { font-size: .68rem; }
        }
        @media (max-width: 576px){
            .app-navbar .container-fluid { gap: .5rem; }
            .home-link { width: 36px; height: 36px; }
            .logo { height: 32px; }
            .guest-actions { width: 100%; }
        }

        .wrap { max-width: 1100px; margin: 24px auto 32px; padding: 0 18px; }
        .card { background:#fff; border:1px solid var(--grey-300); border-radius:10px; padding:20px; margin-bottom:18px; box-shadow:0 1px 3px rgba(0,0,0,.06); }
        h2 { font-size:22px; }
        h3 { font-size:20px; }
        .grid { display:grid; gap:14px; }
        .grid-2 { grid-template-columns: repeat(2, minmax(0,1fr)); }
        .grid-3 { grid-template-columns: repeat(3, minmax(0,1fr)); }
        label { display:block; font-size:16px; color:#374151; margin-bottom:6px; font-weight:600; }
        input, select, textarea { width:100%; padding:12px 14px; border:2px solid #d1d5db; border-radius:8px; background:#fff; font-size:18px; }
        input[type="checkbox"]{ width:auto; transform: scale(1.3); transform-origin: left center; margin-right:8px; }
        input:focus, select:focus, textarea:focus { outline:3px solid rgba(0,104,71,.25); border-color: var(--green); }
        textarea { min-height: 96px; }
        table { width:100%; border-collapse: collapse; }
        th, td { text-align:left; padding:12px; border-bottom:1px solid var(--grey-300); font-size:18px; }
        tbody tr:nth-child(odd){ background: #fafafa; }
        .btn-icon { padding:6px 8px; min-height:auto; line-height:1; }

        .spinner { display:inline-block; width:16px; height:16px; border:2px solid rgba(255,255,255,.6); border-top-color:#fff; border-radius:50%; animation: spin 1s linear infinite; margin-right:8px; vertical-align: text-bottom; }
        .btn-secondary .spinner { border-color: rgba(0,0,0,.3); border-top-color: rgba(0,0,0,.7); }
        @keyframes spin { to { transform: rotate(360deg); } }
        @keyframes navDrop {
            from { transform: translateY(-8px); opacity: .82; }
            to { transform: translateY(0); opacity: 1; }
        }

        .status { padding:12px 14px; border-radius:8px; background:#e9fff1; color:#065f46; border:2px solid #a7f3d0; margin-bottom:12px; }
        .error { padding:12px 14px; border-radius:8px; background:#fff7ed; color:#9a3412; border:2px solid #fed7aa; margin-bottom:12px; }
        .actions-stick { position: sticky; bottom: calc(var(--footer-h) + 8px); background:#fff; padding-top:12px; }

        .btn-support { background: var(--yellow); color:#000; border:2px solid rgba(0,0,0,.08); }

        .backdrop { position: fixed; inset:0; background: rgba(0,0,0,.45); display:none; align-items:center; justify-content:center; padding:12px; z-index: 2001; }
        .backdrop[aria-hidden="false"] { display:flex; }
        .backdrop .modal { display:block !important; position: relative; z-index: 2002; background:#fff; border-radius:12px; width:min(380px, 90vw); height:auto; max-height:none; border: 1px solid var(--grey-300); box-shadow:0 10px 24px rgba(0,0,0,.18); box-sizing: border-box; }
        .backdrop .modal header { background: var(--green); color:#fff; padding:8px 12px; border-radius:10px 10px 0 0; position:relative; box-shadow:none; }
        .backdrop .modal header strong { font-size:15px; }
        .modal .content { padding:12px; font-size:15px; }
        @media (max-width: 576px){ .backdrop .modal { max-height: 50vh; } }
        .modal .actions { display:flex; justify-content:flex-end; gap:6px; padding:0 12px 12px; }
        .close-x { position:absolute; right:12px; top:8px; background:transparent; border:none; color:#fff; font-size:24px; line-height:1; cursor:pointer; }

        .dataTables_wrapper .dataTables_length label,
        .dataTables_wrapper .dataTables_filter label {
            display: flex;
            align-items: center;
            gap: 6px;
            margin: 0;
            font-size: 14px;
            color: #374151;
        }
        .dataTables_wrapper .dataTables_length select,
        .dataTables_wrapper .dataTables_filter input {
            height: 30px;
            padding: .25rem .5rem;
            font-size: .875rem;
            border: 1px solid #d1d5db;
            border-radius: .375rem;
        }
        .dataTables_wrapper .dataTables_filter input { width: 220px; }
        @media (max-width: 768px){
            .dataTables_wrapper .dataTables_filter input { width: 160px; }
            .dataTables_wrapper .dataTables_length select { width: 90px; }
        }
        .dataTables_wrapper .dataTables_info { font-size: 14px; color:#374151; padding-top: .25rem; }
        .dataTables_wrapper .dataTables_paginate { display:flex; gap:6px; align-items:center; padding-top:.25rem; flex-wrap: nowrap; }
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            padding:.25rem .5rem !important;
            font-size:.875rem;
            line-height:1.2;
            border-radius:.375rem !important;
            border:1px solid #d1d5db !important;
            background:#fff !important;
            color:#374151 !important;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: var(--green) !important;
            color:#fff !important;
            border-color: var(--green) !important;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button.disabled { opacity:.5; cursor: default !important; }
        .dataTables_wrapper { width: 100% !important; }
    </style>
    @stack('head')
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
<header class="navbar navbar-expand-lg navbar-dark sticky-top app-navbar">
    <div class="container-fluid">
        <div class="d-flex align-items-center gap-2">
            @if(request()->is('registroVehicular*'))
                <a href="{{ route('public.dashboard') }}" class="home-link" title="Inicio" aria-label="Inicio">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M12 3l9 8h-3v8h-5v-5H11v5H6v-8H3l9-8z"/>
                    </svg>
                </a>
            @endif

            <a class="navbar-brand d-flex align-items-center gap-2" href="{{ auth()->check() ? route('movements.index') : route('public.dashboard') }}">
                <img class="logo" src="{{ asset('images/logo_marca.png') }}" alt="Concreto Lanzado de Fresnillo MARCA" onerror="this.style.display='none'" style="height:36px;">
                <span>
                    @if(request()->is('registroVehicular*'))
                        <strong>Registro Vehicular</strong>
                        <span class="d-block small">Concreto Lanzado de Fresnillo MARCA</span>
                    @else
                        <strong>Concreto Lanzado de Fresnillo MARCA</strong>
                    @endif
                </span>
            </a>
        </div>

        @if(request()->routeIs('public.dashboard') && !auth()->check())
            <a class="btn btn-outline-light btn-sm ms-auto rounded-pill px-3" href="{{ route('login') }}">
                <i class="bi bi-box-arrow-in-right me-1"></i>Iniciar sesión
            </a>
        @endif

        @if(!auth()->check() && request()->is('registroVehicular*') && !request()->routeIs('public.dashboard'))
            <div class="guest-actions ms-auto d-flex align-items-center gap-2">
                <a class="btn btn-outline-light btn-sm px-3" href="{{ route('login') }}">
                    <i class="bi bi-box-arrow-in-right me-1"></i>Entrar
                </a>
                <a class="btn btn-light btn-sm px-3 text-success" href="{{ route('public.dashboard') }}">
                    Dashboard Público
                </a>
            </div>
        @endif

        @auth
            <button class="navbar-toggler ms-auto" type="button" data-bs-toggle="collapse" data-bs-target="#appNavbar" aria-controls="appNavbar" aria-expanded="false" aria-label="Menú">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse app-navbar-collapse" id="appNavbar">
                <div class="navbar-shell">
                    <ul class="navbar-nav main-nav me-auto mb-0">
                        <li class="nav-item">
                            <a class="nav-link {{ request()->routeIs('movements.*') ? 'active' : '' }}" href="{{ route('movements.index') }}">
                                <i class="bi bi-arrow-left-right"></i><span>Movimientos</span>
                            </a>
                        </li>
                        @if(in_array(auth()->user()->role, ['admin','superadmin']))
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('departures.*') ? 'active' : '' }}" href="{{ route('departures.index') }}">
                                    <i class="bi bi-box-arrow-up-right"></i><span>Salidas</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('vehicles.*') ? 'active' : '' }}" href="{{ route('vehicles.index') }}">
                                    <i class="bi bi-truck"></i><span>Vehículos</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('drivers.*') ? 'active' : '' }}" href="{{ route('drivers.index') }}">
                                    <i class="bi bi-person-vcard"></i><span>Conductores</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('maintenance.*') ? 'active' : '' }}" href="{{ route('maintenance.index') }}">
                                    <i class="bi bi-tools"></i><span>Mantenimiento</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('parts.*') ? 'active' : '' }}" href="{{ route('parts.index') }}">
                                    <i class="bi bi-gear-wide-connected"></i><span>Refacciones</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('mechanics.*') ? 'active' : '' }}" href="{{ route('mechanics.index') }}">
                                    <i class="bi bi-wrench-adjustable-circle"></i><span>Mecánicos</span>
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('repairs.*') ? 'active' : '' }}" href="{{ route('repairs.index') }}">
                                    <i class="bi bi-shield-check"></i><span>Reparaciones</span>
                                </a>
                            </li>
                        @endif
                        @if(auth()->user()->role === 'superadmin')
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}">
                                    <i class="bi bi-people"></i><span>Usuarios</span>
                                </a>
                            </li>
                        @endif
                    </ul>

                    <div class="nav-divider d-lg-none"></div>

                    <ul class="navbar-nav align-items-lg-center gap-2 user-nav">
                        <li class="nav-item">
                            <span class="user-pill">
                                <i class="bi bi-person-circle"></i>
                                <span>{{ trim(auth()->user()->name ?? '') !== '' ? auth()->user()->name : auth()->user()->username }}</span>
                            </span>
                        </li>
                        <li class="nav-item">
                            <form action="{{ route('logout') }}" method="POST" class="d-inline">
                                @csrf
                                <button class="btn btn-outline-light btn-sm btn-logout" type="submit">
                                    <i class="bi bi-box-arrow-right me-1"></i>Salir
                                </button>
                            </form>
                        </li>
                    </ul>
                </div>
            </div>
        @endauth
    </div>
</header>
<div class="wrap">
    @if (session('status'))
        <div class="status">{{ session('status') }}</div>
    @endif
    @if ($errors->any())
        <div class="error">
            <strong>Revisa los errores:</strong>
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{ $slot ?? '' }}
    @yield('content')
    <footer class="mt-4 border-top bg-white">
        <div class="container py-3">
            <div class="row g-3 align-items-center">
                <div class="col-12 col-md-6 d-flex align-items-center gap-2">
                    <img class="logo" src="{{ asset('images/logo_marca.png') }}" alt="Concreto Lanzado de Fresnillo MARCA" onerror="this.style.display='none'" style="height:36px;">
                    <div class="small">
                        <strong class="d-block">Concreto Lanzado de Fresnillo MARCA</strong>
                        <span class="d-block">Av Enrique Estrada #755, Las Américas, 99030, Fresnillo, Zacatecas</span>
                        <span class="d-block">Desarrollador: Manuel Hernandez</span>
                    </div>
                </div>
                <div class="col-12 col-md-6 d-flex justify-content-md-end align-items-center gap-3">
                    <div class="d-flex align-items-center gap-2">
                        <a class="btn btn-outline-secondary btn-sm" href="#" aria-label="Facebook"><i class="bi bi-facebook"></i></a>
                        <a class="btn btn-outline-secondary btn-sm" href="#" aria-label="X"><i class="bi bi-twitter-x"></i></a>
                        <a class="btn btn-outline-secondary btn-sm" href="#" aria-label="LinkedIn"><i class="bi bi-linkedin"></i></a>
                    </div>
                    <button type="button" class="btn btn-warning btn-sm" id="btnSupport">Soporte</button>
                </div>
            </div>
        </div>
    </footer>

    <div class="backdrop" id="supportBackdrop" aria-hidden="true" role="dialog" aria-modal="true" aria-labelledby="supportTitle">
        <div class="modal" role="document">
            <header>
                <strong id="supportTitle">Soporte</strong>
                <button class="close-x" type="button" aria-label="Cerrar" id="btnCloseSupport">×</button>
            </header>
            <div class="content">
                Para soporte contacte al área de sistemas.
            </div>
            <div class="actions">
                <button class="btn btn-secondary" type="button" id="btnOkSupport">Entendido</button>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    (function(){
        const openBtn = document.getElementById('btnSupport');
        const closeBtn = document.getElementById('btnCloseSupport');
        const okBtn = document.getElementById('btnOkSupport');
        const backdrop = document.getElementById('supportBackdrop');
        function open(){ backdrop.setAttribute('aria-hidden','false'); }
        function close(){ backdrop.setAttribute('aria-hidden','true'); }
        if(openBtn) openBtn.addEventListener('click', open);
        if(closeBtn) closeBtn.addEventListener('click', close);
        if(okBtn) okBtn.addEventListener('click', close);
        if(backdrop) backdrop.addEventListener('click', function(e){ if(e.target === backdrop) close(); });
        document.addEventListener('keydown', function(e){ if(e.key === 'Escape') close(); });
    })();
    (function(){
        const navbar = document.getElementById('appNavbar');
        if(!navbar || typeof bootstrap === 'undefined') return;
        const collapse = bootstrap.Collapse.getOrCreateInstance(navbar, { toggle: false });
        navbar.querySelectorAll('.nav-link').forEach(function(link){
            link.addEventListener('click', function(){
                if(window.innerWidth < 992 && navbar.classList.contains('show')){
                    collapse.hide();
                }
            });
        });
    })();
    // Deshabilitar botón de envío y mostrar spinner mientras se envÃ­a (para POST/PUT/PATCH/DELETE)
    (function(){
        let lastClickedSubmit = null;
        document.addEventListener('click', function(e){
            const btn = e.target.closest('button[type="submit"], input[type="submit"]');
            if(btn){ lastClickedSubmit = btn; }
        }, true);
        document.addEventListener('submit', function(e){
            const form = e.target;
            if(!(form instanceof HTMLFormElement)) return;
            const method = (form.getAttribute('method') || 'GET').toLowerCase();
            if(method === 'get') return; // no bloquear filtros GET
            const btn = lastClickedSubmit && form.contains(lastClickedSubmit)
                ? lastClickedSubmit
                : form.querySelector('button[type="submit"], input[type="submit"]');
            if(btn && !btn.dataset.loading){
                btn.dataset.loading = '1';
                btn.setAttribute('aria-busy','true');
                btn.disabled = true;
                // Mantener ancho aproximado usando contenido con spinner
                const isButton = btn.tagName === 'BUTTON';
                const original = btn.innerHTML;
                btn.dataset.original = original;
                const label = 'Cargando';
                if(isButton){
                    btn.innerHTML = '<span class="spinner"></span><span>'+label+'</span>';
                } else {
                    btn.value = label;
                }
            }
        }, true);
        // Si el envío es prevenido por JS, reactivar el botón
        document.addEventListener('submit', function(e){
            setTimeout(function(){
                if(e.defaultPrevented && lastClickedSubmit && lastClickedSubmit.dataset.loading){
                    lastClickedSubmit.disabled = false;
                    lastClickedSubmit.removeAttribute('aria-busy');
                    if(lastClickedSubmit.tagName === 'BUTTON' && lastClickedSubmit.dataset.original){
                        lastClickedSubmit.innerHTML = lastClickedSubmit.dataset.original;
                    }
                    delete lastClickedSubmit.dataset.loading;
                }
            });
        });
    })();
    // Modal de conflicto de sesión (cuenta en uso)
    (function(){
        const conflictMsg = @json(session('session_conflict'));
        if(!conflictMsg) return;
        // Crear modal on-the-fly reutilizando estilos existentes
        const backdrop = document.createElement('div');
        backdrop.className = 'backdrop';
        backdrop.setAttribute('aria-hidden','false');
        backdrop.setAttribute('role','dialog');
        backdrop.setAttribute('aria-modal','true');
        const modal = document.createElement('div');
        modal.className = 'modal';
        const header = document.createElement('header');
        header.innerHTML = '<strong>Sesión cerrada</strong>';
        const content = document.createElement('div');
        content.className = 'content';
        content.textContent = conflictMsg;
        const actions = document.createElement('div');
        actions.className = 'actions';
        const ok = document.createElement('button');
        ok.className = 'btn btn-secondary btn-sm';
        ok.textContent = 'Entendido';
        ok.addEventListener('click', ()=> document.body.removeChild(backdrop));
        actions.appendChild(ok);
        modal.appendChild(header); modal.appendChild(content); modal.appendChild(actions);
        backdrop.appendChild(modal);
        document.body.appendChild(backdrop);
    })();
</script>
@stack('scripts')
</body>
</html>
