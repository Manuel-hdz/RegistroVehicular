<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'AplicaciÃ³n')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            padding-top: 56px;
            background: linear-gradient(180deg, #f8fbf9, #eff7f1);
        }

        main.container {
            position: relative;
        }
        .navbar-light-green { background-color: #299c44; } /* Cambio: barra superior en verde claro. */
        .navbar-brand-centered {
            position: static;
            display: inline-flex;
            align-items: center;
            gap: 0.55rem;
            margin-right: 1.5rem;
            font-size: 0.98rem;
            font-weight: 700;
            letter-spacing: 0.01rem;
            line-height: 1.15;
            white-space: nowrap;
        }


        .brand-hero {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            overflow: hidden;
            margin-bottom: 1rem;
            padding: 1.15rem 1.25rem;
            border-radius: 8px;
            color: #ffffff;
            background: linear-gradient(90deg, rgba(25, 93, 48, 0.96), rgba(41, 156, 68, 0.82));
            box-shadow: 0 12px 28px rgba(25, 93, 48, 0.18);
        }

        .brand-hero__label {
            margin-bottom: 0.2rem;
            font-size: 0.78rem;
            font-weight: 700;
            letter-spacing: 0.08rem;
            text-transform: uppercase;
            opacity: 0.86;
        }


        .navbar .nav-link {
            border-radius: 6px;
            padding-left: 0.75rem;
            padding-right: 0.75rem;
            transition: background-color 0.15s ease, color 0.15s ease;
        }

        .navbar .nav-link:hover,
        .navbar .nav-link:focus {
            background-color: rgba(255, 255, 255, 0.14);
            color: #ffffff;
        }

        .floating-table-scroll {
            position: fixed;
            left: 50%;
            bottom: 0.75rem;
            z-index: 1030;
            display: none;
            width: min(96vw, 1140px);
            height: 16px;
            overflow-x: auto;
            overflow-y: hidden;
            transform: translateX(-50%);
            border-radius: 8px;
            background: rgba(255, 255, 255, 0.95);
            box-shadow: 0 8px 24px rgba(33, 37, 41, 0.18);
        }

        .floating-table-scroll.is-visible {
            display: block;
        }

        .floating-table-scroll__inner {
            height: 1px;
        }

        .card,
        .table-responsive,
        .modal-content {
            border: 1px solid rgba(25, 93, 48, 0.1);
            border-radius: 8px;
            box-shadow: 0 12px 30px rgba(33, 37, 41, 0.08);
        }

        .card-body {
            padding: 1.35rem;
        }

        .app-card {
            overflow: hidden;
        }

        .table-responsive {
            background: rgba(255, 255, 255, 0.98);
        }

        .table {
            margin-bottom: 0;
        }

        .table thead th {
            position: sticky;
            top: 0;
            z-index: 2;
            background: #eef8f0;
            color: #195d30;
            border-bottom: 1px solid rgba(25, 93, 48, 0.18);
            font-size: 0.78rem;
            letter-spacing: 0.03rem;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .table tbody tr:hover {
            background-color: rgba(41, 156, 68, 0.06);
        }

        .form-control,
        .form-select {
            border-radius: 8px;
            border-color: rgba(25, 93, 48, 0.2);
        }

        .form-control:focus,
        .form-select:focus {
            border-color: #299c44;
            box-shadow: 0 0 0 0.2rem rgba(41, 156, 68, 0.16);
        }

        .btn {
            border-radius: 8px;
            font-weight: 600;
        }

        .btn-primary,
        .btn-success {
            border-color: #299c44;
            background: linear-gradient(135deg, #299c44, #1f7e38);
        }

        .btn-primary:hover,
        .btn-success:hover {
            border-color: #1f7e38;
            background: linear-gradient(135deg, #23883c, #17642c);
        }

        .btn-outline-primary,
        .btn-outline-success {
            color: #1f7e38;
            border-color: rgba(31, 126, 56, 0.45);
        }

        .btn-outline-primary:hover,
        .btn-outline-success:hover {
            border-color: #299c44;
            background: #299c44;
            color: #ffffff;
        }

        .alert {
            border: 0;
            border-radius: 8px;
            box-shadow: 0 8px 20px rgba(33, 37, 41, 0.06);
        }

        .meter-panel {
            border-color: rgba(41, 156, 68, 0.2) !important;
            background: linear-gradient(180deg, #ffffff, #f4fbf5) !important;
            box-shadow: inset 0 1px 0 rgba(255, 255, 255, 0.9), 0 8px 20px rgba(33, 37, 41, 0.06);
        }

        .badge {
            border-radius: 999px;
            padding: 0.45em 0.7em;
        }

        @media (max-width: 991.98px) {
            /* Cambio responsivo: acomoda la barra en telefono y tablet sin tocar escritorio. */
            body {
                padding-top: 64px;
            }

            .navbar .container {
                display: grid;
                grid-template-columns: 1fr 48px;
                align-items: center;
                gap: 0.5rem;
            }

            .navbar-brand-centered {
                grid-column: 1;
                justify-self: start;
                margin: 0;
                max-width: 100%;
                text-align: left;
                white-space: normal;
                line-height: 1.15;
                font-size: 0.95rem;
            }


            .brand-hero {
                align-items: flex-start;
                padding: 1rem;
            }


            .navbar-toggler {
                grid-column: 2;
                justify-self: end;
            }

            .navbar-collapse {
                grid-column: 1 / -1;
                width: 100%;
                padding-top: 0.75rem;
            }

            main.container {
                max-width: 100%;
                padding-left: 0.75rem;
                padding-right: 0.75rem;
            }

            .card,
            .table-responsive,
            .alert {
                background-color: rgba(255, 255, 255, 0.96);
            }

            .card,
            .alert,
            .form-control,
            .form-select,
            .table,
            .btn {
                overflow-wrap: anywhere;
            }

            .responsive-card-table {
                border-collapse: separate;
                border-spacing: 0 0.75rem;
            }

            .responsive-card-table thead {
                display: none;
            }

            .responsive-card-table,
            .responsive-card-table tbody,
            .responsive-card-table tr,
            .responsive-card-table td {
                display: block;
                width: 100%;
            }

            .responsive-card-table tr {
                background: #ffffff;
                border: 1px solid #dee2e6;
                border-radius: 8px;
                box-shadow: 0 4px 14px rgba(33, 37, 41, 0.08);
                padding: 0.5rem 0;
            }

            .responsive-card-table td {
                border: 0;
                padding: 0.45rem 0.85rem;
            }

            .responsive-card-table td::before {
                content: attr(data-label);
                display: block;
                color: #6c757d;
                font-size: 0.78rem;
                font-weight: 700;
                text-transform: uppercase;
            }

            .responsive-card-table img {
                max-width: 100%;
                height: auto;
            }

            .mobile-actions {
                display: grid !important;
                gap: 0.5rem;
            }

            .mobile-actions .btn,
            .mobile-actions form,
            .mobile-actions button {
                width: 100%;
            }

            .btn-group {
                display: grid;
                grid-template-columns: repeat(2, minmax(0, 1fr));
                width: 100%;
            }

            .btn-group .btn {
                min-width: 0;
            }
        }

        @media (max-width: 767.98px) {
            /* Cambio responsivo: en telefono se reducen espacios para aprovechar la pantalla. */
            .card-body {
                padding: 1rem;
            }

            .login-image {
                width: 150px !important;
                height: 150px !important;
            }

            .brand-hero {
                display: block;
            }


            .d-grid.d-sm-flex > .btn,
            .d-grid.d-sm-flex > form,
            .d-grid.d-sm-flex > form > button {
                width: 100%;
            }
        }
    </style>
    {{-- Permite que vistas especificas carguen sus estilos sin afectar todo el layout. --}}
    @stack('styles')
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark navbar-light-green fixed-top">
        <div class="container">
            <a class="navbar-brand navbar-brand-centered" href="{{ url('/') }}">Concreto Lanzado Fresnillo</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    @auth
                        @if(auth()->user()->isAdministrador())
                            <li class="nav-item"><a class="nav-link" href="{{ route('empleados.index') }}">Unidades</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ route('empleados.importar-csv') }}">Importar CSV</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ route('empleados.carga-masiva') }}">Carga masiva</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ route('empleados.fotos-masivas') }}">Fotos masivas</a></li>
                            <li class="nav-item"><a class="nav-link" href="{{ route('empleados.catalogo-qr') }}">Catalogo QR</a></li>
                        @endif
                        <li class="nav-item"><a class="nav-link" href="{{ route('detector-qr') }}">Detector QR</a></li>
                        @if(auth()->user()->isSuperadministrador())
                            <li class="nav-item"><a class="nav-link" href="{{ route('register') }}">Usuarios</a></li>
                        @endif
                        <li class="nav-item">
                            {{-- Boton para cerrar la sesion actual. --}}
                            <form action="{{ route('logout') }}" method="POST" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-link nav-link">Salir</button>
                            </form>
                            
                        </li>
                    @else
                        <li class="nav-item"><a class="nav-link" href="{{ route('login') }}">Login</a></li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

  
    <main class="container my-4">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @yield('content')
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        (() => {
            const tables = Array.from(document.querySelectorAll('.table-responsive'));

            if (tables.length === 0) {
                return;
            }

            const floatingScroll = document.createElement('div');
            const floatingInner = document.createElement('div');
            let activeTable = null;
            let syncing = false;

            floatingScroll.className = 'floating-table-scroll';
            floatingInner.className = 'floating-table-scroll__inner';
            floatingScroll.appendChild(floatingInner);
            document.body.appendChild(floatingScroll);

            const scrollableTables = () => tables.filter((table) => table.scrollWidth > table.clientWidth + 1);

            const tableInView = (table) => {
                const rect = table.getBoundingClientRect();
                return rect.top < window.innerHeight - 48 && rect.bottom > 96;
            };

            const setActiveTable = () => {
                activeTable = scrollableTables().find(tableInView) || null;

                if (!activeTable) {
                    floatingScroll.classList.remove('is-visible');
                    return;
                }

                floatingInner.style.width = `${activeTable.scrollWidth}px`;
                floatingScroll.scrollLeft = activeTable.scrollLeft;
                floatingScroll.classList.add('is-visible');
            };

            floatingScroll.addEventListener('scroll', () => {
                if (!activeTable || syncing) {
                    return;
                }

                syncing = true;
                activeTable.scrollLeft = floatingScroll.scrollLeft;
                syncing = false;
            });

            tables.forEach((table) => {
                table.addEventListener('scroll', () => {
                    if (table !== activeTable || syncing) {
                        return;
                    }

                    syncing = true;
                    floatingScroll.scrollLeft = table.scrollLeft;
                    syncing = false;
                });
            });

            window.addEventListener('scroll', setActiveTable, { passive: true });
            window.addEventListener('resize', setActiveTable);
            setActiveTable();
        })();
    </script>
    {{-- Permite que vistas especificas carguen scripts propios al final del body. --}}
    @stack('scripts')
</body>
</html>
