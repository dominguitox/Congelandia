<header class="navbar navbar-dark fixed-top shadow-sm app-header">
    <div class="container-fluid p-0 d-flex align-items-center">

        <a class="navbar-brand fw-bold mb-0 h1" href="/">Congelandia</a>

        <div class="ms-auto text-white">
            <span class="me-2" >{{ auth()->user()->nombre ?? 'Nombre del usuario' }}</span>
            <span class="badge bg-info text-dark">{{ auth()->user()->rol ?? 'Rol' }}</span>
        </div>
    </div>
</header>