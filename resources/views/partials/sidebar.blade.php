<nav class="sidebar" id="sidebar">

    @if(auth()->check() && auth()->user()->rol)

        <ul class="nav flex-column p-3">

            <li class="nav-item">
                <a href="{{ url('/dashboard') }}" class="nav-link text-white fw-bold">
                    Inicio
                </a>
            </li>


            <li class="nav-item">
                <a href="{{ url('/pos') }}" class="nav-link text-white fw-bold">
                    Punto de Venta
                </a>
            </li>


            <li class="nav-item">
                <a href="{{ url('/inventario') }}" class="nav-link text-white fw-bold">
                    Inventario
                </a>
            </li>


            <li class="nav-item">
                <a href="{{ url('/historial') }}" class="nav-link text-white fw-bold">
                    Historial
                </a>
            </li>


            <li class="nav-item">
                <a href="{{ route('clientes.index') }}" class="nav-link text-white fw-bold">
                    Clientes
                </a>
            </li>


            <li class="nav-item">
                <a href="{{ url('/proveedores') }}" class="nav-link text-white fw-bold">
                    Proveedores
                </a>
            </li>


            <li class="nav-item">
                <a href="{{ url('/categorias') }}" class="nav-link text-white fw-bold">
                    Categorías
                </a>
            </li>


            <li class="nav-item">
                <a href="{{ url('/usuarios') }}" class="nav-link text-white fw-bold">
                    Usuarios
                </a>
            </li>


            <li class="nav-item">
                <a href="{{ url('/Roles') }}" class="nav-link text-white fw-bold">
                    Roles
                </a>
            </li>


            @if(auth()->user()->rol === 'Administrador')

                <li class="nav-item">
                    <a href="{{ url('/reportes') }}" class="nav-link text-white fw-bold">
                        Reportes
                    </a>
                </li>

            @endif


            <li class="nav-item mt-3">

                <form method="POST" action="{{ route('logout') }}">

                    @csrf

                    <button type="submit"
                        class="w-full text-left flex items-center gap-2 px-4 py-2 text-red-600 hover:bg-gray-100">

                        Cerrar sesión

                    </button>

                </form>

            </li>


        </ul>

    @endif

</nav>