<header class="navbar navbar-dark fixed-top shadow-sm"
    style="background-color: var(--color-navy-dark, #0a192f); height: 60px; z-index: 1010; padding: 0 20px;">
    <div class="container-fluid p-0 d-flex align-items-center">

        <a class="navbar-brand fw-bold mb-0 h1" href="/">Congelandia</a>

        <div class="ms-auto text-white">
            <span class="me-2" >{{ auth()->user()->nombre ?? 'Nombre del usuario' }}</span>
            <span class="badge bg-info text-dark">{{ auth()->user()->rol ?? 'Rol' }}</span>
        </div>
    </div>
</header>