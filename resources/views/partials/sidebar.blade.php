<nav class="sidebar" id="sidebar">
    <ul class="nav flex-column p-3">
        <li class="nav-item mb-2">
            <a href="{{ url('/dashboard') }}" class="nav-link text-white fw-bold">Inicio</a>
        </li>
        <li class="nav-item mb-2">
            <a href="{{ url('/pos') }}" class="nav-link text-white fw-bold">Sistema de Ventas</a>
        </li>
        <li class="nav-item mb-2">
            <a href="{{ url('/inventario') }}" class="nav-link text-white fw-bold">Inventario</a>
        </li>
        <li class="nav-item mb-2">
            <a href="{{ url('/reportes') }}" class="nav-link text-white fw-bold">Reportes</a>
        </li>


        <!-- Oculto para cajeros, visible para admin -->
        @if(auth()->check() && auth()->user()->rol === 'admin')

        @endif
    </ul>
</nav>