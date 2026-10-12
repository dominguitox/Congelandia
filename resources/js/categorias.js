document.addEventListener("DOMContentLoaded", function () {
    // 1. LÓGICA DEL PANEL LATERAL (Ver Productos)
    const btnVerProductos = document.querySelectorAll('.btn-ver-productos');
    const panelesDetalle = document.querySelectorAll('.panel-detalle');

    btnVerProductos.forEach(boton => {
        boton.addEventListener('click', function () {
            panelesDetalle.forEach(panel => panel.classList.add('d-none'));
            const targetId = this.getAttribute('data-target');
            document.querySelector(targetId).classList.remove('d-none');
        });
    });

    // 2. LÓGICA DEL MODAL EDITAR
    const btnEditar = document.querySelectorAll('.btn-editar');
    const formEditar = document.getElementById('formEditar');
    const inputEditarNombre = document.getElementById('inputEditarNombre');

    btnEditar.forEach(boton => {
        boton.addEventListener('click', function () {
            const id = this.getAttribute('data-id');
            const nombre = this.getAttribute('data-nombre');

            formEditar.action = `/categorias/${id}`;
            inputEditarNombre.value = nombre;
        });
    });

    // 3. LÓGICA DEL MODAL ELIMINAR
    const btnEliminar = document.querySelectorAll('.btn-eliminar');
    const formEliminar = document.getElementById('formEliminar');
    const textoEliminarNombre = document.getElementById('textoEliminarNombre');
    const alertaEliminar = document.getElementById('alertaEliminar');
    const btnSubmitEliminar = document.getElementById('btnSubmitEliminar');

    btnEliminar.forEach(boton => {
        boton.addEventListener('click', function () {
            const id = this.getAttribute('data-id');
            const nombre = this.getAttribute('data-nombre');
            const count = parseInt(this.getAttribute('data-count'));

            formEliminar.action = `/categorias/${id}`;
            textoEliminarNombre.textContent = nombre;

            if (count > 0) {
                alertaEliminar.classList.remove('d-none');
                alertaEliminar.innerHTML = `<strong>Atención:</strong> Esta categoría tiene <b>${count} producto(s)</b> asociado(s). No se puede eliminar por reglas de integridad.`;
                btnSubmitEliminar.disabled = true;
            } else {
                alertaEliminar.classList.add('d-none');
                btnSubmitEliminar.disabled = false;
            }
        });
    });
});