<nav class="nav nav-tabs mb-3" aria-label="Mantenimiento">
    <a class="nav-link {{ request()->routeIs('maintenance.index') ? 'active' : '' }}" href="{{ route('maintenance.index') }}">Estado de unidades</a>
    <a class="nav-link {{ request()->routeIs('maintenance.backlog.*') ? 'active' : '' }}" href="{{ route('maintenance.backlog.index') }}">BackLog</a>
</nav>
