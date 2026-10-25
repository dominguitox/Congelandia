import { Modal } from 'bootstrap';

window.editarProducto = function (boton) {
    document.getElementById('edit_codigo').value = boton.dataset.codigo;
    document.getElementById('edit_nombre').value = boton.dataset.nombre;
    document.getElementById('edit_idCategoria').value = boton.dataset.categoria;
    document.getElementById('edit_precioVenta').value = boton.dataset.precio;
    document.getElementById('edit_descripcion').value = boton.dataset.descripcion;

    document.getElementById('formEditarProducto').action = '/productos/' + boton.dataset.codigo;
};

document.addEventListener('DOMContentLoaded', () => {
    const modalIngreso = document.getElementById('modalRegistrarIngreso');
    const modalSalida = document.getElementById('modalRegistrarSalida');
    const productoSalida = document.getElementById('salida_codigoProducto');
    const cantidadSalida = document.getElementById('salida_cantidad');

    modalIngreso?.addEventListener('show.bs.modal', (event) => {
        const codigo = event.relatedTarget?.dataset.codigo;
        const select = document.getElementById('ingreso_codigoProducto');

        if (codigo && select) {
            select.value = codigo;
        }
    });

    const actualizarLimiteSalida = () => {
        const stock = Number(productoSalida?.selectedOptions[0]?.dataset.stock ?? 0);
        if (cantidadSalida) {
            cantidadSalida.max = String(stock);
        }
    };

    productoSalida?.addEventListener('change', actualizarLimiteSalida);
    modalSalida?.addEventListener('show.bs.modal', actualizarLimiteSalida);

    const modalConErrores = document.querySelector('[data-modal-error]')?.dataset.modalError;
    const modal = modalConErrores ? document.getElementById(modalConErrores) : null;

    if (modal) {
        Modal.getOrCreateInstance(modal).show();
    }
});
