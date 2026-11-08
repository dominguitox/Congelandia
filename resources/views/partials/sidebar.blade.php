<nav class="sidebar" id="sidebar">
    @if(auth()->check() && auth()->user()->rol)
        @php
            // Definimos los elementos del menú de forma estructurada
            $menuItems = [
                ['label' => 'Inicio', 'url' => route('dashboard.index'), 'pattern' => 'dashboard.*'],
                ['label' => 'Punto de Venta', 'url' => route('pos.index'), 'pattern' => 'pos.*'],
                ['label' => 'Inventario', 'url' => route('inventario.index'), 'pattern' => 'inventario.*'],
                ['label' => 'Historial', 'url' => route('historial.index'), 'pattern' => 'historial.*'],
                ['label' => 'Clientes', 'url' => route('clientes.index'), 'pattern' => 'clientes.*'],
                ['label' => 'Proveedores', 'url' => route('proveedores.index'), 'pattern' => 'proveedores.*'],
                ['label' => 'Categorías', 'url' => route('categorias.index'), 'pattern' => 'categorias.*'],
                ['label' => 'Usuarios', 'url' => route('usuarios.index'), 'pattern' => 'usuarios.*'],
            ];

            // Agregamos reportes solo si es Administrador
            if (auth()->user()->rol === 'Administrador') {
                $menuItems[] = ['label' => 'Reportes', 'url' => route('reportes.index'), 'pattern' => 'reportes.*'];
            }
        @endphp

        <ul class="nav flex-column p-3">
            @foreach($menuItems as $item)
                @php
                    // Comprobamos si está activo según corresponda (ruta nombrada o patrón de URL)
                    $isActive = request()->routeIs($item['pattern']);
                @endphp

                <li class="nav-item">
                    <a href="{{ $item['url'] }}"
                        class="nav-link text-white fw-bold {{ $isActive ? 'link-sidebar-seleccionado' : '' }}">
                        {{ $item['label'] }}
                    </a>
                </li>
            @endforeach

            {{-- Botón de Cerrar Sesión fuera del loop --}}
            <li class="nav-item mt-3">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="sidebar-logout">
                        Cerrar sesión
                    </button>
                </form>
            </li>
        </ul>
    @endif
</nav>