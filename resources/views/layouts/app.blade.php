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
            --green: #006847; /* Verde bandera */
            --yellow: #FFCD11; /* Amarillo Caterpillar */
            --grey-900: #2F2F2F; /* Gris base oscuro */
            --grey-100: #F3F4F6; /* Fondo claro */
            --grey-300: #E5E7EB; /* Bordes */
            --footer-h: 68px; /* alto footer fijo */
        }
        * { box-sizing: border-box; }
        html, body { overscroll-behavior-y: contain; }
        /* Cubre el área de rebote superior sin alterar el alto del header */
        body::before { content:""; position: fixed; top:0; left:0; right:0; height: 28px; background: var(--green); z-index: 1059; pointer-events:none; }
        body { font-family: system-ui, -apple-system, Segoe UI, Roboto, sans-serif; margin:0; background:var(--grey-100); color:var(--grey-900); font-size:18px; line-height:1.5; }
        header { position: sticky; top:0; z-index:10; background:var(--green); color:#fff; padding:10px 18px; display:flex; align-items:center; justify-content:space-between; box-shadow:0 2px 6px rgba(0,0,0,.15); }
        .brand { display:flex; align-items:center; gap:12px; }
        .brand strong { font-size:20px; letter-spacing:.3px; line-height: 1.1; }
        .brand .sub { display:block; font-size:12px; opacity:.9; font-weight:500; }
        /* Compactar navbar y títulos */
        header.navbar { padding: 8px 12px; position: sticky; top: 0; z-index: 1060; background: var(--green); }
        .navbar-brand strong { font-size: 18px; line-height: 1.1; }
        .navbar-brand .small { font-size: 12px; opacity: .9; line-height: 1; }
        .logo { height:44px; width:auto; display:block; filter: drop-shadow(0 1px 1px rgba(0,0,0,.15)); background:transparent; }
        /* Estilo opcional para links del menú principal (scoped) */
        .main-nav .nav-link { color:#e8f5ec; font-weight:600; }
        .main-nav .nav-link.active, .main-nav .nav-link:hover { color:#ffffff; border-bottom:3px solid var(--yellow); }
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
        /* Evitar override de clases Bootstrap .btn*, mantenemos solo utilidades propias */
        /* .btn, .btn-primary, .btn-secondary, .btn-warning, .btn-link redefinidas aquÃ­ anteriormente */
        .btn-icon { padding:6px 8px; min-height:auto; line-height:1; }
        /* Spinner para estado de carga */
        .spinner { display:inline-block; width:16px; height:16px; border:2px solid rgba(255,255,255,.6); border-top-color:#fff; border-radius:50%; animation: spin 1s linear infinite; margin-right:8px; vertical-align: text-bottom; }
        .btn-secondary .spinner { border-color: rgba(0,0,0,.3); border-top-color: rgba(0,0,0,.7); }
        @keyframes spin { to { transform: rotate(360deg); } }
        .home-link { display:inline-flex; align-items:center; justify-content:center; width:36px; height:36px; border-radius:8px; background:rgba(255,255,255,.15); color:#fff; margin-right:6px; border:2px solid rgba(255,255,255,.25); }
        .home-link:hover { background: rgba(255,255,255,.22); }
        /* Evitar override de .row de Bootstrap */
        /* .row { display:flex; gap:10px; align-items:center; flex-wrap: wrap; } */
        .status { padding:12px 14px; border-radius:8px; background:#e9fff1; color:#065f46; border:2px solid #a7f3d0; margin-bottom:12px; }
        .error { padding:12px 14px; border-radius:8px; background:#fff7ed; color:#9a3412; border:2px solid #fed7aa; margin-bottom:12px; }
        .actions-stick { position: sticky; bottom: calc(var(--footer-h) + 8px); background:#fff; padding-top:12px; }

        /* Footer (no fijo) */
        .btn-support { background: var(--yellow); color:#000; border:2px solid rgba(0,0,0,.08); }

        /* Modal soporte */
        .backdrop { position: fixed; inset:0; background: rgba(0,0,0,.45); display:none; align-items:center; justify-content:center; padding:16px; }
        .backdrop[aria-hidden="false"] { display:flex; }
        .modal { background:#fff; border-radius:12px; width:min(520px, 92vw); border: 2px solid var(--grey-300); box-shadow:0 10px 30px rgba(0,0,0,.2); }
        .modal header { background: var(--green); color:#fff; padding:12px 16px; border-radius:10px 10px 0 0; position:relative; box-shadow:none; }
        .modal .content { padding:18px; font-size:18px; }
        .modal .actions { display:flex; justify-content:flex-end; gap:10px; padding:0 18px 18px; }
        .close-x { position:absolute; right:12px; top:8px; background:transparent; border:none; color:#fff; font-size:24px; line-height:1; cursor:pointer; }
            /* DataTables */
        .dataTables_wrapper .dataTables_length label,
        .dataTables_wrapper .dataTables_filter label {
            display: flex; align-items: center; gap: 6px; margin: 0; font-size: 14px; color: #374151;
        }
        .dataTables_wrapper .dataTables_length select,
        .dataTables_wrapper .dataTables_filter input {
            height: 30px; padding: .25rem .5rem; font-size: .875rem; border: 1px solid #d1d5db; border-radius: .375rem;
        }
        .dataTables_wrapper .dataTables_filter input { w
        @media (max-width: 768px){
            .dataTables_wrapper .dataTables_filter input { width: 160px; }
            .dataTables_wrapper .dataTables_length select { width: 90px; }
        }
idth: 220px; }
        @media (max-width: 768px){
            .dataTables_wrapper .dataTables_filter input { width: 160px; }
            .dataTables_wrapper .dataTables_length select { width: 90px; }
        }
        .dataTables_wrapper .dataTables_info { font-size: 14px; color:#374151; padding-top: .25rem; }
        .dataTables_wrapper .dataTables_paginate { display:flex; gap:6px; align-items:center; padding-top:.25rem; flex-wrap: nowrap; }
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            padding:.25rem .5rem !important; font-size:.875rem; line-height:1.2; border-radius:.375rem !important;
            border:1px solid #d1d5db !important; background:#fff !important; color:#374151 !important;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button.current {
            background: var(--green) !important; color:#fff !important; border-color: var(--green) !important;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button.disabled { opacity:.5; cursor: default !important; }        .dataTables_wrapper { width: 100% !important; }    </style>
    @stack('head')
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
<header class="navbar navbar-expand-lg navbar-dark sticky-top" style="background:var(--green)">
  <div class="container-fluid">
    @if(request()->is('registroVehicular*'))
      <a href="{{ route('public.dashboard') }}" class="home-link d-inline-flex me-2" title="Inicio" aria-label="Inicio">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
          <path d="M12 3l9 8h-3v8h-5v-5H11v5H6v-8H3l9-8z"/>
        </svg>
      </a>
    @endif

    <a class="navbar-brand d-flex align-items-center gap-2" href="#">
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

    @if(request()->routeIs('public.dashboard') && !auth()->check())
      <a class="btn btn-outline-light btn-sm ms-auto" href="{{ route('login') }}">
        <i class="bi bi-box-arrow-in-right me-1"></i>Iniciar sesión
      </a>
    @endif

    @auth<button class="navbar-toggler ms-auto" type="button" data-bs-toggle="collapse" data-bs-target="#appNavbar" aria-controls="appNavbar" aria-expanded="false" aria-label="Menú">
      <span class="navbar-toggler-icon"></span>
    </button>

    <div class="collapse navbar-collapse" id="appNavbar">
      <ul class="navbar-nav main-nav me-auto mb-2 mb-lg-0">
        @auth
          <li class="nav-item"><a class="nav-link {{ request()->routeIs('movements.*') ? 'active' : '' }}" href="{{ route('movements.index') }}">Movimientos</a></li>
          @if(in_array(auth()->user()->role, ['admin','superadmin']))
            <li class="nav-item"><a class="nav-link {{ request()->routeIs('departures.*') ? 'active' : '' }}" href="{{ route('departures.index') }}">Salidas</a></li>
            <li class="nav-item"><a class="nav-link {{ request()->routeIs('vehicles.*') ? 'active' : '' }}" href="{{ route('vehicles.index') }}">Vehículos</a></li>
            <li class="nav-item"><a class="nav-link {{ request()->routeIs('drivers.*') ? 'active' : '' }}" href="{{ route('drivers.index') }}">Conductores</a></li>
            <li class="nav-item"><a class="nav-link {{ request()->routeIs('maintenance.*') ? 'active' : '' }}" href="{{ route('maintenance.index') }}">Mantenimiento</a></li>
            <li class="nav-item"><a class="nav-link {{ request()->routeIs('parts.*') ? 'active' : '' }}" href="{{ route('parts.index') }}">Refacciones</a></li>
            <li class="nav-item"><a class="nav-link {{ request()->routeIs('mechanics.*') ? 'active' : '' }}" href="{{ route('mechanics.index') }}">Mecánicos</a></li>
            <li class="nav-item"><a class="nav-link {{ request()->routeIs('repairs.*') ? 'active' : '' }}" href="{{ route('repairs.index') }}">Reparaciones</a></li>
          @endif
          @if(auth()->user()->role === 'superadmin')
            <li class="nav-item"><a class="nav-link {{ request()->routeIs('users.*') ? 'active' : '' }}" href="{{ route('users.index') }}">Usuarios</a></li>
          @endif
        @endauth
      </ul>

      <ul class="navbar-nav ms-auto align-items-lg-center gap-2">
        @auth
          <li class="nav-item">
            <span class="navbar-text fw-semibold">{{ trim(auth()->user()->name ?? '') !== '' ? auth()->user()->name : auth()->user()->username }}</span>
          </li>
          <li class="nav-item">
            <form action="{{ route('logout') }}" method="POST" class="d-inline">
              @csrf
              <button class="btn btn-outline-light btn-sm" type="submit"><i class="bi bi-box-arrow-right me-1"></i>Salir</button>
            </form>
          </li>
        @else
          @if(request()->is('registroVehicular*'))
            <li class="nav-item"><a class="btn btn-outline-light btn-sm" href="{{ route('login') }}">Entrar</a></li>
            <li class="nav-item"><a class="btn btn-link text-white-50" href="{{ route('public.dashboard') }}">Dashboard Público</a></li>
          @endif
        @endauth
      </ul>    </div>    @endauth
    </div>
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
    // Modal de conflicto de sesiÃ³n (cuenta en uso)
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
        header.innerHTML = '<strong>SesiÃ³n cerrada</strong>';
        const content = document.createElement('div');
        content.className = 'content';
        content.textContent = conflictMsg;
        const actions = document.createElement('div');
        actions.className = 'actions';
        const ok = document.createElement('button');
        ok.className = 'btn btn-secondary';
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





