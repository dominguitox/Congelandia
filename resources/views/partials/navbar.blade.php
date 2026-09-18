<nav class="navbar navbar-expand-lg navbar-dark fixed-top"
    style="background-color: var(--color-navy-dark); height: 60px; z-index: 1010; padding: 0 20px;">
    <div class="container-fluid p-0">

        <!-- Botón para abrir/cerrar el Sidebar que definimos antes -->
        <button id="toggle-sidebar" class="btn text-white border-0 me-3" style="font-size: 24px; padding: 0;">
            ☰
        </button>

        <!-- Logo o Nombre del Proyecto -->
        <a class="navbar-brand fw-bold mb-0 h1" href="/">Congelandia</a>

        <!-- Botón hamburguesa nativo de Bootstrap (visible solo en pantallas muy pequeñas) -->
        <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse"
            data-bs-target="#opcionesUsuario" aria-controls="opcionesUsuario" aria-expanded="false"
            aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Opciones de la derecha (Usuario y Logout) -->
        <div class="collapse navbar-collapse justify-content-end" id="opcionesUsuario">
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle text-white d-flex align-items-center" href="#" id="menuUsuario"
                        role="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <!-- Muestra el nombre del usuario logueado -->
                        <span class="me-2">👤 {{ auth()->user()->name ?? 'Usuario' }}</span>

                        <!-- Muestra el rol como una pequeña etiqueta -->
                        <span class="badge bg-info text-dark">
                            {{ auth()->user()->rol ?? 'Rol' }}
                        </span>
                    </a>

                    <!-- Menú desplegable -->
                    <ul class="dropdown-menu dropdown-menu-end shadow" aria-labelledby="menuUsuario">
                        <li><a class="dropdown-item" href="#">Mi Perfil</a></li>
                        <li>
                            <hr class="dropdown-divider">
                        </li>
                        <li>
                            <!-- Formulario de cerrado de sesión estándar en Laravel -->
                            <form action="" method="POST" class="m-0">
                                @csrf
                                <button type="submit" class="dropdown-item text-danger fw-bold">
                                    Cerrar Sesión
                                </button>
                            </form>
                        </li>
                    </ul>
                </li>
            </ul>
        </div>

    </div>
</nav>