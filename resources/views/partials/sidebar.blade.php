<nav class="sidebar" id="sidebar">
    <!-- Solo cuando está logeado -->
    @if(auth()->check() && auth()->user()->rol)
        <ul class="nav flex-column p-3">
            <li class="nav-item ">
                <a href="{{ url('/dashboard') }}" class="nav-link text-white fw-bold">Inicio</a>
            </li>
            <li class="nav-item ">
                <a href="{{ url('/pos') }}" class="nav-link text-white fw-bold">Punto de Venta</a>
            </li>
            <li class="nav-item ">
                <a href="{{ url('/inventario') }}" class="nav-link text-white fw-bold">Inventario</a>
            </li>
            <li class="nav-item ">
                <a href="{{ url('/historial') }}" class="nav-link text-white fw-bold">Historial</a>
            </li>
            <li class="nav-item ">
                <a href="{{ url('/cliente') }}" class="nav-link text-white fw-bold">Clientes</a>
            </li>
            <li class="nav-item ">
                <a href="{{ url('/proveedores') }}" class="nav-link text-white fw-bold">Proveedores</a>
            </li>
            <li class="nav-item ">
                <a href="{{ url('/categorias') }}" class="nav-link text-white fw-bold">Categorias</a>
            </li>
            <li class="nav-item ">
                <a href="{{ url('/usuarios') }}" class="nav-link text-white fw-bold">Usuarios</a>
            </li>
            <li class="nav-item ">
                <a href="{{ url('/Roles') }}" class="nav-link text-white fw-bold">Roles</a>
            </li>
    @endif

        <!-- Oculto para cajeros, visible para admin -->
        @if(auth()->check() && auth()->user()->rol === 'Administrador')
            <li class="nav-item mb-2">
                <a href="{{ url('/reportes') }}" class="nav-link text-white fw-bold">Reportes</a>
            </li>
        @endif
        @if(auth()->check() && auth()->user()->rol)

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit"
                    class="w-full text-left flex items-center gap-2 px-4 py-2 text-red-600 hover:bg-gray-100">
                    <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round"
                            d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                    </svg>
                    Cerrar <br>
                    sesión
                </button>
            </form>
        @endif



    </ul>
</nav>