window.editarProducto = function (boton) {
    document.getElementById('edit_codigo').value = boton.dataset.codigo;
    document.getElementById('edit_nombre').value = boton.dataset.nombre;
    document.getElementById('edit_idCategoria').value = boton.dataset.categoria;
    document.getElementById('edit_precioVenta').value = boton.dataset.precio;
    document.getElementById('edit_descripcion').value = boton.dataset.descripcion;

    document.getElementById('formEditarProducto').action = '/productos/' + boton.dataset.codigo;
};

window.registrarIngresoSingle = function (boton) {
    document.getElementById('ingreso_codigo').value = boton.dataset.codigo;
    document.getElementById('ingreso_nombre').value = boton.dataset.nombre;
    document.getElementById('ingreso_idCategoria').value = boton.dataset.categoria;
    document.getElementById('ingreso_precioVenta').value = boton.dataset.precio;
    document.getElementById('ingreso_descripcion').value = boton.dataset.descripcion;

    document.getElementById('formRegistrarIngreso').action = '/ingresos/' + boton.dataset.codigo;
};  