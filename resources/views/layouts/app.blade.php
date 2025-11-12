<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Registro Vehicular</title>
    @stack('head-pre')
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
        body { font-family: system-ui, -apple-system, Segoe UI, Roboto, sans-serif; margin:0; background:var(--grey-100); color:var(--grey-900); font-size:18px; line-height:1.5; padding-bottom: var(--footer-h); }
        header { position: sticky; top:0; z-index:10; background:var(--green); color:#fff; padding:10px 18px; display:flex; align-items:center; justify-content:space-between; box-shadow:0 2px 6px rgba(0,0,0,.15); }
        .brand { display:flex; align-items:center; gap:12px; }
        .brand strong { font-size:20px; letter-spacing:.3px; line-height: 1.1; }
        .brand .sub { display:block; font-size:12px; opacity:.9; font-weight:500; }
        .logo { height:44px; width:auto; display:block; filter: drop-shadow(0 1px 1px rgba(0,0,0,.15)); background:transparent; }
        nav a { color:#e8f5ec; margin-right:16px; text-decoration:none; padding:8px 6px; border-bottom:3px solid transparent; font-weight:600; }
        nav a.active, nav a:hover { color:#ffffff; border-bottom-color: var(--yellow); }
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
        .btn { display:inline-block; padding:12px 18px; border-radius:10px; text-decoration:none; cursor:pointer; border:2px solid transparent; font-weight:700; font-size:18px; min-height:44px; }
        .btn-primary { background:var(--green); color:#fff; }
        .btn-primary:hover { filter: brightness(1.05); }
        .btn-secondary { background:#ffffff; color:var(--grey-900); border-color: var(--grey-300); }
        .btn-secondary:hover { background:#f9fafb; }
        .btn-warning { background: var(--yellow); color:#000; }
        .btn-link { color: var(--green); text-decoration: none; font-weight:700; }
        /* Spinner para estado de carga */
        .spinner { display:inline-block; width:16px; height:16px; border:2px solid rgba(255,255,255,.6); border-top-color:#fff; border-radius:50%; animation: spin 1s linear infinite; margin-right:8px; vertical-align: text-bottom; }
        .btn-secondary .spinner { border-color: rgba(0,0,0,.3); border-top-color: rgba(0,0,0,.7); }
        @keyframes spin { to { transform: rotate(360deg); } }
        .home-link { display:inline-flex; align-items:center; justify-content:center; width:36px; height:36px; border-radius:8px; background:rgba(255,255,255,.15); color:#fff; margin-right:6px; border:2px solid rgba(255,255,255,.25); }
        .home-link:hover { background: rgba(255,255,255,.22); }
        .row { display:flex; gap:10px; align-items:center; flex-wrap: wrap; }
        .status { padding:12px 14px; border-radius:8px; background:#e9fff1; color:#065f46; border:2px solid #a7f3d0; margin-bottom:12px; }
        .error { padding:12px 14px; border-radius:8px; background:#fff7ed; color:#9a3412; border:2px solid #fed7aa; margin-bottom:12px; }
        .actions-stick { position: sticky; bottom: calc(var(--footer-h) + 8px); background:#fff; padding-top:12px; }

        /* Footer fijo */
        footer.footer-fixed { position: fixed; left:0; right:0; bottom:0; height: var(--footer-h); background:#fff; border-top: 4px solid var(--green); display:flex; align-items:center; }
        .footer-inner { max-width:1100px; margin:0 auto; width:100%; padding:8px 18px; display:flex; gap:12px; align-items:center; justify-content:space-between; flex-wrap:wrap; }
        .footer-info { display:flex; gap:12px; align-items:center; }
        .footer-text small { display:block; line-height:1.2; }
        .btn-support { background: var(--yellow); color:#000; border:2px solid rgba(0,0,0,.08); }

        /* Modal soporte */
        .backdrop { position: fixed; inset:0; background: rgba(0,0,0,.45); display:none; align-items:center; justify-content:center; padding:16px; }
        .backdrop[aria-hidden="false"] { display:flex; }
        .modal { background:#fff; border-radius:12px; width:min(520px, 92vw); border: 2px solid var(--grey-300); box-shadow:0 10px 30px rgba(0,0,0,.2); }
        .modal header { background: var(--green); color:#fff; padding:12px 16px; border-radius:10px 10px 0 0; position:relative; box-shadow:none; }
        .modal .content { padding:18px; font-size:18px; }
        .modal .actions { display:flex; justify-content:flex-end; gap:10px; padding:0 18px 18px; }
        .close-x { position:absolute; right:12px; top:8px; background:transparent; border:none; color:#fff; font-size:24px; line-height:1; cursor:pointer; }
    </style>
    @stack('head')
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
<header>
    <div class="brand">
        <a href="{{ route('public.dashboard') }}" class="home-link" title="Inicio" aria-label="Inicio">
            <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                <path d="M12 3l9 8h-3v8h-5v-5H11v5H6v-8H3l9-8z"/>
            </svg>
        </a>
        <img class="logo" src="{{ asset('images/logo_marca.png') }}" alt="Concreto Lanzado de Fresnillo MARCA" onerror="this.style.display='none'">
        <div>
            <strong>Registro Vehicular</strong>
            <span class="sub">Concreto Lanzado de Fresnillo MARCA</span>
        </div>
    </div>
    <nav>
        @auth
            <a href="{{ route('movements.index') }}" class="{{ request()->routeIs('movements.*') ? 'active' : '' }}">Movimientos</a>
            @if(in_array(auth()->user()->role, ['admin','superadmin']))
                <a href="{{ route('departures.index') }}" class="{{ request()->routeIs('departures.*') ? 'active' : '' }}">Salidas</a>
                <a href="{{ route('vehicles.index') }}" class="{{ request()->routeIs('vehicles.*') ? 'active' : '' }}">Vehículos</a>
                <a href="{{ route('drivers.index') }}" class="{{ request()->routeIs('drivers.*') ? 'active' : '' }}">Conductores</a>
            @endif
            @if(auth()->user()->role === 'superadmin')
                <a href="{{ route('users.index') }}" class="{{ request()->routeIs('users.*') ? 'active' : '' }}">Usuarios</a>
            @endif
        @endauth
    </nav>
    <div>
        @auth
            <span style="margin-right:10px; font-weight:600;">{{ trim(auth()->user()->name ?? '') !== '' ? auth()->user()->name : auth()->user()->username }}</span>
            <form action="{{ route('logout') }}" method="POST" style="display:inline;">
                @csrf
                <button class="btn btn-secondary" type="submit">Salir</button>
            </form>
        @else
            <a class="btn btn-secondary" href="{{ route('login') }}">Entrar</a>
            <a class="btn btn-link" href="{{ route('public.dashboard') }}">Dashboard Público</a>
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
    <footer class="footer-fixed">
        <div class="footer-inner">
            <div class="footer-info">
                <img class="logo" src="{{ asset('images/logo_marca.png') }}" alt="Concreto Lanzado de Fresnillo MARCA" onerror="this.style.display='none'" style="height:36px;">
                <div class="footer-text">
                    <strong>Concreto Lanzado de Fresnillo MARCA</strong>
                    <small>Av Enrique Estrada #755, Las Américas, 99030, Fresnillo, Zacatecas</small>
                    <small>Desarrollador: Manuel Hernandez</small>
                </div>
            </div>
            <div class="footer-actions">
                <button type="button" class="btn btn-support" id="btnSupport">Soporte</button>
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
    // Deshabilitar botón de envío y mostrar spinner mientras se envía (para POST/PUT/PATCH/DELETE)
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
                const label = 'Cargando…';
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
</script>
@stack('scripts')
</body>
</html>
