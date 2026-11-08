<nav class="sidebar" id="sidebar">
    @if(auth()->check() && auth()->user()->rol)
@php
    $menuItems = [
        ['label' => 'Inicio', 'url' => route('dashboard.index'), 'pattern' => 'dashboard.*'],
        ['label' => 'Punto de Venta', 'url' => route('pos.index'), 'pattern' => 'pos.*'],
        ['label' => 'Inventario', 'url' => route('inventario.index'), 'pattern' => 'inventario.*', 'admin' => true],
        ['label' => 'Historial', 'url' => route('historial.index'), 'pattern' => 'historial.*', 'admin' => true],
        ['label' => 'Clientes', 'url' => route('clientes.index'), 'pattern' => 'clientes.*', 'admin' => true],
        ['label' => 'Proveedores', 'url' => route('proveedores.index'), 'pattern' => 'proveedores.*', 'admin' => true],
        ['label' => 'Categorías', 'url' => route('categorias.index'), 'pattern' => 'categorias.*', 'admin' => true],
        ['label' => 'Usuarios', 'url' => route('usuarios.index'), 'pattern' => 'usuarios.*', 'admin' => true],
        ['label' => 'Reportes', 'url' => route('reportes.index'), 'pattern' => 'reportes.*', 'admin' => true],
    ];
@endphp

        <ul class="nav flex-column p-3">
            @foreach($menuItems as $item)
                @continue(($item['admin'] ?? false) && ! auth()->user()->tieneRol('Administrador'))
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