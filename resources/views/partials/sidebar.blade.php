<nav class="sidebar" id="sidebar">
    @if(auth()->check() && auth()->user()->rol)
        @php
            // Definimos los elementos del menú de forma estructurada
            $menuItems = [
                ['label' => 'Inicio', 'url' => url('/dashboard'), 'pattern' => 'dashboard'],
                ['label' => 'Punto de Venta', 'url' => url('/pos'), 'pattern' => 'pos*'],
                ['label' => 'Inventario', 'url' => url('/inventario'), 'pattern' => 'inventario*'],
                ['label' => 'Historial', 'url' => url('/historial'), 'pattern' => 'historial*'],
                ['label' => 'Clientes', 'url' => route('clientes.index'), 'pattern' => 'clientes.*', 'isRoute' => true],
                ['label' => 'Proveedores', 'url' => url('/proveedores'), 'pattern' => 'proveedores*'],
                ['label' => 'Categorías', 'url' => url('/categorias'), 'pattern' => 'categorias*'],
                ['label' => 'Usuarios', 'url' => url('/usuarios'), 'pattern' => 'usuarios*'],
                ['label' => 'Roles', 'url' => url('/Roles'), 'pattern' => 'Roles*'],
            ];

            // Agregamos reportes solo si es Administrador
            if (auth()->user()->rol === 'Administrador') {
                $menuItems[] = ['label' => 'Reportes', 'url' => url('/reportes'), 'pattern' => 'reportes*'];
            }
        @endphp

        <ul class="nav flex-column p-3">
            @foreach($menuItems as $item)
                @php
                    // Comprobamos si está activo según corresponda (ruta nombrada o patrón de URL)
                    $isActive = isset($item['isRoute'])
                        ? request()->routeIs($item['pattern'])
                        : request()->is($item['pattern']);
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
                    <button type="submit"
                        class="w-full text-left flex items-center gap-2 px-4 py-2 text-red-600 hover:bg-gray-100 rounded">
                        Cerrar sesión
                    </button>
                </form>
            </li>
        </ul>
    @endif
</nav>