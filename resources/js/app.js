import 'bootstrap';

/* =========================================================
   MENU LATERAL EN MOVIL
   =========================================================
   El sidebar permanece oculto en pantallas pequeñas y se
   muestra como panel deslizante al pulsar el botón del header.
   ========================================================= */

document.addEventListener('DOMContentLoaded', function () {

    const sidebar = document.getElementById('sidebar');
    const toggle = document.getElementById('btnSidebar');
    const overlay = document.getElementById('sidebarOverlay');

    if (!sidebar || !toggle) {
        return;
    }

    function sidebarAbierto() {
        return sidebar.classList.contains('sidebar--abierto');
    }

    function abrirSidebar() {
        sidebar.classList.add('sidebar--abierto');
        toggle.setAttribute('aria-expanded', 'true');

        if (overlay) {
            overlay.classList.add('activo');
        }

        document.body.classList.add('sidebar-abierto');
    }

    function cerrarSidebar() {
        sidebar.classList.remove('sidebar--abierto');
        toggle.setAttribute('aria-expanded', 'false');

        if (overlay) {
            overlay.classList.remove('activo');
        }

        document.body.classList.remove('sidebar-abierto');
    }

    toggle.addEventListener('click', function () {
        if (sidebarAbierto()) {
            cerrarSidebar();
        } else {
            abrirSidebar();
        }
    });

    if (overlay) {
        overlay.addEventListener('click', cerrarSidebar);
    }

    document.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && sidebarAbierto()) {
            cerrarSidebar();
        }
    });

    sidebar.querySelectorAll('a').forEach(function (enlace) {
        enlace.addEventListener('click', cerrarSidebar);
    });

    window.addEventListener('resize', function () {
        if (window.innerWidth >= 992 && sidebarAbierto()) {
            cerrarSidebar();
        }
    });
});

window.toggleDetalle = function(id, boton) {


    const fila = document.getElementById(
        'detalle-' + id
    );


    if (fila.style.display === "table-row") {


        fila.style.display = "none";

        boton.innerHTML = "▼";


    } else {


        fila.style.display = "table-row";

        boton.innerHTML = "▲";

    }

}
window.toggleFormularioCliente = function(){

    const formulario = document.getElementById(
        'formularioCliente'
    );


    if(formulario.style.display === "none"){

        formulario.style.display = "block";

    }
    else{

        formulario.style.display = "none";

    }

}
