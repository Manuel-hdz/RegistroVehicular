<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sistema de Administracion Integral</title>
    @stack('head-pre')
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        :root{
            --green: #006847;
            --green-700: #005b3e;
            --green-800: #004a33;
            --green-900: #033624;
            --yellow: #FFCD11;
            --cream: #fbf9f2;
            --surface: rgba(255,255,255,.86);
            --surface-strong: #ffffff;
            --grey-900: #203129;
            --grey-700: #4a5f56;
            --grey-500: #6d8178;
            --grey-100: #eef3ef;
            --grey-200: #dde7e1;
            --grey-300: #cfdbd4;
            --footer-h: 68px;
            --ring: 0 0 0 4px rgba(0, 104, 71, .13);
            --shadow-soft: 0 18px 50px rgba(16, 52, 37, .10);
            --shadow-card: 0 12px 30px rgba(17, 48, 36, .08);
        }
        * { box-sizing: border-box; }
        html, body { overscroll-behavior-y: contain; }
        body::before {
            content:"";
            position: fixed;
            inset: 0 0 auto 0;
            height: 26px;
            background: linear-gradient(90deg, var(--green-900), var(--green));
            z-index: 1059;
            pointer-events:none;
        }
        body::after {
            content:"";
            position: fixed;
            inset: 0;
            background:
                radial-gradient(circle at top left, rgba(255,205,17,.10), transparent 24rem),
                radial-gradient(circle at top right, rgba(0,104,71,.10), transparent 26rem),
                linear-gradient(180deg, #f6f9f7, #eef3ef 38%, #edf2ef 100%);
            z-index: -2;
            pointer-events:none;
        }
        body {
            font-family: "Manrope", "Segoe UI", sans-serif;
            margin:0;
            background:var(--grey-100);
            color:var(--grey-900);
            font-size:16px;
            line-height:1.6;
            letter-spacing: -.01em;
        }
        a { text-decoration: none; }

        .app-navbar {
            z-index: 1060;
            padding: .7rem .85rem;
            background: linear-gradient(112deg, #033725, var(--green) 58%, #0f7f59);
            border-bottom: 1px solid rgba(255,255,255,.16);
            box-shadow: 0 20px 34px rgba(0, 62, 43, .24);
            backdrop-filter: blur(14px);
            animation: navDrop .45s ease;
        }
        .app-navbar .container-fluid { gap: .75rem; align-items: center; }
        .navbar-brand { margin-right: 0; color: #fff !important; }
        .navbar-brand strong { font-size: 1.04rem; line-height: 1.05; letter-spacing: -.02em; font-weight: 800; }
        .navbar-brand .small { font-size: .72rem; opacity: .82; letter-spacing: .02em; }
        .logo { height:42px; width:auto; display:block; filter: drop-shadow(0 4px 12px rgba(0,0,0,.16)); background:transparent; }

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
        .home-link.active {
            background: linear-gradient(180deg, #ffe38c, var(--yellow));
            color: #04281b;
            border-color: rgba(0,0,0,.1);
            box-shadow: 0 4px 10px rgba(0,0,0,.16);
        }
        .brand-shortcut .dropdown-menu {
            border-radius: 18px;
            border: 1px solid rgba(8, 48, 33, .10);
            background: rgba(247,255,249,.96);
            padding: .45rem;
            min-width: 220px;
            box-shadow: 0 20px 34px rgba(0,0,0,.14);
            backdrop-filter: blur(10px);
        }
        .brand-shortcut .dropdown-item {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            border-radius: 12px;
            font-size: .88rem;
            font-weight: 700;
            color: #13412f;
            padding: .58rem .72rem;
        }
        .brand-shortcut .dropdown-item:hover,
        .brand-shortcut .dropdown-item:focus {
            color: #0e2f22;
            background: #e6f7ec;
        }
        .brand-shortcut .dropdown-item.active,
        .brand-shortcut .dropdown-item:active {
            color: #04281b;
            background: #c8f0d5;
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
            font-weight: 700;
            font-size: .88rem;
            border-radius: 999px;
            padding: .55rem .82rem;
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
        .main-nav .dropdown-toggle::after {
            margin-left: .25rem;
            vertical-align: middle;
        }
        .main-nav .dropdown-menu {
            border-radius: 18px;
            border: 1px solid rgba(8, 48, 33, .10);
            background: rgba(247,255,249,.96);
            padding: .45rem;
            min-width: 228px;
            box-shadow: 0 20px 34px rgba(0,0,0,.14);
            backdrop-filter: blur(10px);
        }
        .main-nav .dropdown-item {
            display: inline-flex;
            align-items: center;
            gap: .45rem;
            border-radius: 12px;
            font-size: .88rem;
            font-weight: 700;
            color: #13412f;
            padding: .58rem .72rem;
            transition: all .18s ease;
        }
        .main-nav .dropdown-item:hover,
        .main-nav .dropdown-item:focus {
            color: #0e2f22;
            background: #e6f7ec;
        }
        .main-nav .dropdown-item.active,
        .main-nav .dropdown-item:active {
            color: #04281b;
            background: #c8f0d5;
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
            .main-nav .dropdown-menu {
                position: static !important;
                float: none;
                min-width: 100%;
                margin-top: .35rem;
                padding: .35rem;
                background: rgba(255,255,255,.12);
                border-color: rgba(255,255,255,.24);
                box-shadow: none;
            }
            .main-nav .dropdown-item {
                color: #f2fff7;
            }
            .main-nav .dropdown-item:hover,
            .main-nav .dropdown-item:focus {
                color: #ffffff;
                background: rgba(255,255,255,.2);
            }
            .main-nav .dropdown-item.active,
            .main-nav .dropdown-item:active {
                color: #04281b;
                background: linear-gradient(180deg, #ffe38c, var(--yellow));
            }
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

        .wrap {
            position: relative;
            max-width: 1180px;
            margin: 28px auto 40px;
            padding: 0 20px;
        }
        .card {
            background: var(--surface);
            border: 1px solid rgba(16, 52, 37, .08);
            border-radius: 24px;
            padding: 22px 22px 20px;
            margin-bottom: 18px;
            box-shadow: var(--shadow-card);
            backdrop-filter: blur(10px);
        }
        h1, h2, h3, h4, h5 { letter-spacing: -.03em; color: #16362a; }
        h2 { font-size: 1.45rem; font-weight: 800; }
        h3 { font-size: 1.15rem; font-weight: 800; }
        h4, h5 { font-weight: 800; }
        p { color: var(--grey-700); }
        .grid { display:grid; gap:16px; }
        .grid-2 { grid-template-columns: repeat(2, minmax(0,1fr)); }
        .grid-3 { grid-template-columns: repeat(3, minmax(0,1fr)); }
        label {
            display:block;
            font-size:.83rem;
            text-transform: uppercase;
            letter-spacing: .08em;
            color: var(--grey-500);
            margin-bottom:8px;
            font-weight:800;
        }
        input, select, textarea {
            width:100%;
            padding: .88rem 1rem;
            border:1px solid rgba(16, 52, 37, .22);
            border-radius: 16px;
            background: #ffffff;
            font-size: .96rem;
            color: #16362a;
            box-shadow: inset 0 1px 0 rgba(255,255,255,.7), 0 1px 2px rgba(16, 52, 37, .04);
            transition: border-color .18s ease, box-shadow .18s ease, transform .18s ease;
        }
        input::placeholder, textarea::placeholder { color: #7d9188; }
        input[type="checkbox"]{ width:auto; transform: scale(1.15); transform-origin: left center; margin-right:8px; }
        input:focus, select:focus, textarea:focus {
            outline: none;
            box-shadow: var(--ring);
            border-color: rgba(0, 104, 71, .52);
        }
        select option { color: #16362a; background: #ffffff; }
        textarea { min-height: 108px; }
        small { color: var(--grey-500); }
        .table-responsive {
            border-radius: 20px;
            border: 1px solid rgba(16, 52, 37, .08);
            overflow: hidden;
            background: var(--surface-strong);
        }
        table { width:100%; border-collapse: separate; border-spacing: 0; }
        th, td {
            text-align:left;
            padding: 14px 16px;
            border-bottom:1px solid rgba(16, 52, 37, .07);
            font-size:.94rem;
            vertical-align: middle;
        }
        th {
            background: #f6faf7;
            color: var(--grey-500);
            font-size: .78rem;
            text-transform: uppercase;
            letter-spacing: .08em;
            font-weight: 800;
        }
        tbody tr:nth-child(odd){ background: #fcfefd; }
        tbody tr:hover { background: #f3fbf6; }
        .btn-icon { padding:6px 8px; min-height:auto; line-height:1; }
        .btn {
            --bs-btn-font-weight: 800;
            --bs-btn-font-size: .9rem;
            --bs-btn-padding-x: 1rem;
            --bs-btn-padding-y: .72rem;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: .45rem;
            border-radius: 999px;
            min-height: 44px;
            white-space: nowrap;
            box-shadow: 0 10px 18px rgba(16, 52, 37, .08);
            border-width: 1px;
            color: inherit;
        }
        .btn:hover { transform: translateY(-1px); }
        .btn:focus-visible { box-shadow: var(--ring); }
        .btn-primary {
            --bs-btn-bg: linear-gradient(180deg, #0d8a5f, var(--green));
            --bs-btn-border-color: rgba(0, 72, 49, .08);
            --bs-btn-hover-bg: linear-gradient(180deg, #0a7c56, var(--green-700));
            --bs-btn-hover-border-color: rgba(0, 72, 49, .12);
            --bs-btn-active-bg: var(--green-700);
            --bs-btn-active-border-color: var(--green-700);
            color: #ffffff !important;
            background: linear-gradient(180deg, #0d8a5f, var(--green)) !important;
            border-color: rgba(0, 72, 49, .14) !important;
            text-shadow: 0 1px 0 rgba(0,0,0,.12);
        }
        .btn-secondary,
        .btn-outline-secondary {
            --bs-btn-bg: rgba(255,255,255,.92);
            --bs-btn-border-color: rgba(16, 52, 37, .10);
            --bs-btn-color: #204236;
            --bs-btn-hover-bg: #f1f7f3;
            --bs-btn-hover-border-color: rgba(16, 52, 37, .18);
            --bs-btn-hover-color: #16362a;
            --bs-btn-active-bg: #e8f1ec;
            --bs-btn-active-border-color: rgba(16, 52, 37, .18);
            box-shadow: none;
            background: #ffffff !important;
            color: #173629 !important;
            border-color: rgba(16, 52, 37, .18) !important;
        }
        .btn-warning,
        .btn-support {
            --bs-btn-bg: linear-gradient(180deg, #ffe684, var(--yellow));
            --bs-btn-border-color: rgba(0,0,0,.08);
            --bs-btn-color: #1b1b18;
            --bs-btn-hover-bg: linear-gradient(180deg, #ffdf57, #f5be00);
            --bs-btn-hover-border-color: rgba(0,0,0,.08);
            background: linear-gradient(180deg, #ffe684, var(--yellow)) !important;
            color: #1b1b18 !important;
            border-color: rgba(80, 58, 0, .16) !important;
        }
        .btn-outline-light {
            --bs-btn-color: #ffffff;
            --bs-btn-border-color: rgba(255,255,255,.26);
            --bs-btn-hover-bg: rgba(255,255,255,.12);
            --bs-btn-hover-border-color: rgba(255,255,255,.32);
            color: #ffffff !important;
            border-color: rgba(255,255,255,.34) !important;
            background: rgba(255,255,255,.08) !important;
        }
        .btn-light {
            background: #ffffff !important;
            color: #173629 !important;
            border-color: rgba(16, 52, 37, .12) !important;
        }
        .btn-outline-secondary:hover,
        .btn-secondary:hover,
        .btn-light:hover {
            background: #f3f8f5 !important;
            color: #102d22 !important;
        }
        .row {
            --bs-gutter-x: .9rem;
            --bs-gutter-y: .9rem;
        }

        .spinner { display:inline-block; width:16px; height:16px; border:2px solid rgba(255,255,255,.6); border-top-color:#fff; border-radius:50%; animation: spin 1s linear infinite; margin-right:8px; vertical-align: text-bottom; }
        .btn-secondary .spinner { border-color: rgba(0,0,0,.3); border-top-color: rgba(0,0,0,.7); }
        @keyframes spin { to { transform: rotate(360deg); } }
        @keyframes navDrop {
            from { transform: translateY(-8px); opacity: .82; }
            to { transform: translateY(0); opacity: 1; }
        }

        .status,
        .error {
            padding: 14px 16px;
            border-radius: 18px;
            margin-bottom: 14px;
            font-weight: 600;
            box-shadow: var(--shadow-card);
        }
        .status { background:#eafbf1; color:#065f46; border:1px solid #bfe8cf; }
        .error { background:#fff7ed; color:#9a3412; border:1px solid #fed7aa; }
        .actions-stick {
            position: sticky;
            bottom: calc(var(--footer-h) + 10px);
            background: linear-gradient(180deg, rgba(255,255,255,.88), rgba(255,255,255,.98));
            backdrop-filter: blur(8px);
            padding-top: 12px;
            padding-bottom: 4px;
            z-index: 5;
        }

        footer {
            border-top: 1px solid rgba(16, 52, 37, .08) !important;
            background: rgba(255,255,255,.86) !important;
            border-radius: 28px 28px 0 0;
            box-shadow: 0 -8px 24px rgba(16, 52, 37, .04);
        }

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
            height: 38px;
            padding: .35rem .7rem;
            font-size: .875rem;
            border: 1px solid rgba(16, 52, 37, .12);
            border-radius: .8rem;
        }
        .dataTables_wrapper .dataTables_filter input { width: 220px; }
        @media (max-width: 768px){
            .dataTables_wrapper .dataTables_filter input { width: 160px; }
            .dataTables_wrapper .dataTables_length select { width: 90px; }
        }
        .dataTables_wrapper .dataTables_info { font-size: 14px; color:#374151; padding-top: .25rem; }
        .dataTables_wrapper .dataTables_paginate { display:flex; gap:6px; align-items:center; padding-top:.25rem; flex-wrap: nowrap; }
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            padding:.35rem .72rem !important;
            font-size:.875rem;
            line-height:1.2;
            border-radius:.8rem !important;
            border:1px solid rgba(16, 52, 37, .12) !important;
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
        @media (max-width: 991.98px){
            .card { border-radius: 22px; padding: 18px; }
            .grid-3 { grid-template-columns: repeat(2, minmax(0,1fr)); }
        }
        @media (max-width: 767.98px){
            body { font-size: 15px; }
            .wrap { padding: 0 14px; margin-top: 22px; }
            .grid-2, .grid-3 { grid-template-columns: minmax(0,1fr); }
            .btn { --bs-btn-padding-x: .9rem; --bs-btn-padding-y: .68rem; width: auto; }
            .actions-stick {
                position: static;
                background: transparent;
                backdrop-filter: none;
                padding-top: 0;
            }
        }
    </style>
    @stack('head')
    <meta name="csrf-token" content="{{ csrf_token() }}">
</head>
<body>
@php($isSystemArea = request()->routeIs(
    'login',
    'login.post',
    'logout',
    'public.dashboard',
    'dashboard.graph.*',
    'movements.*',
    'departures.*',
    'maintenance.*',
    'repairs.*',
    'mechanics.*',
    'vehicles.*',
    'users.*',
    'hr.*',
    'personnel.*',
    'cardex.*',
    'drivers.*',
    'parts.*',
    'comedor.*',
    'cost-centers.*',
    'vacation-policies.*',
    'bulk-imports.*',
    'requisitions.*'
))
@php($canAccessConfiguration = auth()->check() && trim(mb_strtolower(\Illuminate\Support\Str::ascii((string) auth()->user()->department), 'UTF-8')) === 'sistemas')
<header class="navbar navbar-expand-lg navbar-dark sticky-top app-navbar">
    <div class="container-fluid">
        <div class="d-flex align-items-center gap-2">
            @if($isSystemArea)
                <a href="{{ route('public.dashboard') }}" class="home-link" title="Inicio" aria-label="Inicio">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                        <path d="M12 3l9 8h-3v8h-5v-5H11v5H6v-8H3l9-8z"/>
                    </svg>
                </a>
            @endif

            <a class="navbar-brand d-flex align-items-center gap-2" href="{{ auth()->check() ? route('movements.index') : route('public.dashboard') }}">
                <img class="logo" src="{{ asset('images/logo_marca.png') }}" alt="Concreto Lanzado de Fresnillo MARCA" onerror="this.style.display='none'" style="height:36px;">
                <span>
                    @if($isSystemArea)
                        <strong>Sistema de Administracion Integral</strong>
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

        @if(!auth()->check() && $isSystemArea && !request()->routeIs('public.dashboard'))
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
                    @php($isAdminMenu = in_array(auth()->user()->role, ['admin','superadmin']))
                    <ul class="navbar-nav main-nav me-auto mb-0">
                        @if($isAdminMenu)
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle {{ request()->routeIs('movements.*') || request()->routeIs('departures.*') ? 'active' : '' }}" href="#" role="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                                    <i class="bi bi-arrow-left-right"></i><span>Registro Vehicular</span>
                                </a>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a class="dropdown-item {{ request()->routeIs('movements.*') ? 'active' : '' }}" href="{{ route('movements.index') }}">
                                            <i class="bi bi-arrow-left-right"></i><span>Movimientos</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item {{ request()->routeIs('departures.*') ? 'active' : '' }}" href="{{ route('departures.index') }}">
                                            <i class="bi bi-box-arrow-up-right"></i><span>Salidas</span>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        @else
                            <li class="nav-item">
                                <a class="nav-link {{ request()->routeIs('movements.*') ? 'active' : '' }}" href="{{ route('movements.index') }}">
                                    <i class="bi bi-arrow-left-right"></i><span>Movimientos</span>
                                </a>
                            </li>
                        @endif

                        @if($isAdminMenu)
                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle {{ request()->routeIs('maintenance.*') || request()->routeIs('repairs.*') || request()->routeIs('mechanics.*') ? 'active' : '' }}" href="#" role="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                                    <i class="bi bi-tools"></i><span>Mantenimiento</span>
                                </a>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a class="dropdown-item {{ request()->routeIs('maintenance.*') ? 'active' : '' }}" href="{{ route('maintenance.index') }}">
                                            <i class="bi bi-tools"></i><span>Mantenimiento</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item {{ request()->routeIs('vehicles.*') ? 'active' : '' }}" href="{{ route('vehicles.index') }}">
                                            <i class="bi bi-truck"></i><span>Consulta de unidades</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item {{ request()->routeIs('repairs.*') ? 'active' : '' }}" href="{{ route('repairs.index') }}">
                                            <i class="bi bi-shield-check"></i><span>Reparaciones</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item {{ request()->routeIs('parts.*') ? 'active' : '' }}" href="{{ route('parts.index') }}">
                                            <i class="bi bi-gear-wide-connected"></i><span>Refacciones</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item {{ request()->routeIs('mechanics.*') ? 'active' : '' }}" href="{{ route('mechanics.index') }}">
                                            <i class="bi bi-wrench-adjustable-circle"></i><span>Mecánicos</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item {{ request()->routeIs('requisitions.pending') ? 'active' : '' }}" href="{{ route('requisitions.pending') }}">
                                            <i class="bi bi-clipboard-check"></i><span>Pendientes</span>
                                        </a>
                                    </li>
                                </ul>
                            </li>

                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle {{ request()->routeIs('vehicles.*') ? 'active' : '' }}" href="#" role="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                                    <i class="bi bi-building-gear"></i><span>Administración</span>
                                </a>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a class="dropdown-item {{ request()->routeIs('vehicles.*') ? 'active' : '' }}" href="{{ route('vehicles.index') }}">
                                            <i class="bi bi-truck"></i><span>Vehículos</span>
                                        </a>
                                    </li>
                                </ul>
                            </li>

                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle {{ request()->routeIs('hr.*') || request()->routeIs('personnel.*') || request()->routeIs('cardex.*') || request()->routeIs('drivers.*') || request()->routeIs('comedor.*') ? 'active' : '' }}" href="#" role="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                                    <i class="bi bi-people"></i><span>Recursos Humanos</span>
                                </a>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a class="dropdown-item {{ request()->routeIs('personnel.*') ? 'active' : '' }}" href="{{ route('personnel.index') }}">
                                            <i class="bi bi-person-lines-fill"></i><span>Personal</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item {{ request()->routeIs('cardex.index') ? 'active' : '' }}" href="{{ route('cardex.index') }}">
                                            <i class="bi bi-calendar3-week"></i><span>Kardex</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item {{ request()->routeIs('cardex.import.*') ? 'active' : '' }}" href="{{ route('cardex.import.index') }}">
                                            <i class="bi bi-file-earmark-arrow-up"></i><span>Cargar documentos</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item {{ request()->routeIs('drivers.*') ? 'active' : '' }}" href="{{ route('drivers.index') }}">
                                            <i class="bi bi-person-vcard"></i><span>Conductores</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item {{ request()->routeIs('comedor.records') ? 'active' : '' }}" href="{{ route('comedor.records') }}">
                                            <i class="bi bi-cup-hot"></i><span>Comedor</span>
                                        </a>
                                    </li>
                                </ul>
                            </li>

                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle {{ request()->routeIs('parts.*') ? 'active' : '' }}" href="#" role="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                                    <i class="bi bi-box-seam"></i><span>Almacén</span>
                                </a>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a class="dropdown-item {{ request()->routeIs('parts.*') ? 'active' : '' }}" href="{{ route('parts.index') }}">
                                            <i class="bi bi-gear-wide-connected"></i><span>Refacciones</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item {{ request()->routeIs('vehicles.*') ? 'active' : '' }}" href="{{ route('vehicles.index') }}">
                                            <i class="bi bi-truck"></i><span>Unidades</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item {{ request()->routeIs('requisitions.pending') ? 'active' : '' }}" href="{{ route('requisitions.pending') }}">
                                            <i class="bi bi-clipboard-check"></i><span>Pendientes</span>
                                        </a>
                                    </li>
                                </ul>
                            </li>

                            <li class="nav-item dropdown">
                                <a class="nav-link dropdown-toggle {{ request()->routeIs('requisitions.pending') ? 'active' : '' }}" href="#" role="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                                    <i class="bi bi-bag-check"></i><span>Compras</span>
                                </a>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a class="dropdown-item {{ request()->routeIs('requisitions.pending') ? 'active' : '' }}" href="{{ route('requisitions.pending') }}">
                                            <i class="bi bi-clock-history"></i><span>Pendientes</span>
                                        </a>
                                    </li>
                                </ul>
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
                        @if($canAccessConfiguration)
                            <li class="nav-item dropdown brand-shortcut">
                                <a
                                    href="#"
                                    class="home-link dropdown-toggle {{ request()->routeIs('cost-centers.*') || request()->routeIs('bulk-imports.*') || request()->routeIs('vacation-policies.*') ? 'active' : '' }}"
                                    title="Configuración"
                                    aria-label="Configuración"
                                    data-bs-toggle="dropdown"
                                    data-bs-auto-close="outside"
                                    aria-expanded="false"
                                >
                                    <i class="bi bi-gear-fill"></i>
                                </a>
                                <ul class="dropdown-menu dropdown-menu-end">
                                    <li>
                                        <a class="dropdown-item {{ request()->routeIs('vacation-policies.*') ? 'active' : '' }}" href="{{ route('vacation-policies.index') }}">
                                            <i class="bi bi-calendar-heart"></i><span>Tabla de vacaciones</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item {{ request()->routeIs('cost-centers.*') ? 'active' : '' }}" href="{{ route('cost-centers.index') }}">
                                            <i class="bi bi-diagram-3"></i><span>Centros de costos</span>
                                        </a>
                                    </li>
                                    <li>
                                        <a class="dropdown-item {{ request()->routeIs('bulk-imports.*') ? 'active' : '' }}" href="{{ route('bulk-imports.index') }}">
                                            <i class="bi bi-file-earmark-spreadsheet"></i><span>Cargas masivas</span>
                                        </a>
                                    </li>
                                </ul>
                            </li>
                        @endif
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
        navbar.querySelectorAll('.nav-link, .dropdown-item').forEach(function(link){
            link.addEventListener('click', function(){
                if (
                    link.classList.contains('dropdown-toggle') ||
                    link.getAttribute('data-bs-toggle') === 'dropdown' ||
                    link.getAttribute('href') === '#'
                ) {
                    return;
                }
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
