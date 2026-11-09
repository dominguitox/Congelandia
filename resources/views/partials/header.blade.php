<header class="navbar navbar-dark fixed-top shadow-sm app-header">
    <div class="container-fluid p-0 d-flex align-items-center">

        @if(auth()->check())
            <button
                type="button"
                class="app-menu-btn"
                id="btnSidebar"
                aria-label="Abrir menú de navegación"
                aria-controls="sidebar"
                aria-expanded="false"
            >
                ☰
            </button>
        @endif

        <a class="navbar-brand fw-bold mb-0 h1" href="/">Congelandia</a>

        <div class="ms-auto text-white app-user">
            <span class="app-user-name me-2">{{ auth()->user()->nombre ?? 'Nombre del usuario' }}</span>
            <span class="badge bg-info text-dark">{{ auth()->user()->rol ?? 'Rol' }}</span>
        </div>
    </div>
</header>