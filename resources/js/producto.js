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

    const modalOfertas = document.getElementById('modalOfertas');
    if (modalOfertas && !modalConErrores && window.location.hash === '#ofertas') {
        Modal.getOrCreateInstance(modalOfertas).show();
    }

    modalOfertas?.addEventListener('hidden.bs.modal', () => {
        if (window.location.hash === '#ofertas') {
            window.history.replaceState(null, '', window.location.pathname + window.location.search);
        }
    });

    const modalGestionOfertas = document.getElementById('modalGestionOfertas');
    const modalCrearOferta = document.getElementById('modalCrearOferta');
    const modalEditarOferta = document.getElementById('modalEditarOferta');
    const gestionarOfertas = document.getElementById('gestionar-ofertas');

    if (window.location.hash === '#gestionar-ofertas' && modalGestionOfertas) {
        Modal.getOrCreateInstance(modalGestionOfertas).show();
    }

    document.querySelectorAll('.js-editar-oferta').forEach((boton) => {
        boton.removeAttribute('data-bs-toggle');
        boton.removeAttribute('data-bs-target');
        boton.addEventListener('click', () => {
            const form = document.getElementById('formEditarOferta');
            form.action = `/ofertas/${boton.dataset.id}`;
            document.getElementById('editarOfertaId').value = boton.dataset.id;
            document.getElementById('editarOfertaProducto').value = boton.dataset.codigo;
            document.getElementById('editarOfertaDescuento').value = boton.dataset.descuento;
            document.getElementById('editarOfertaInicio').value = boton.dataset.inicio;
            document.getElementById('editarOfertaFin').value = boton.dataset.fin;

            modalGestionOfertas.addEventListener('hidden.bs.modal', () => {
                Modal.getOrCreateInstance(modalEditarOferta).show();
            }, { once: true });
            Modal.getOrCreateInstance(modalGestionOfertas).hide();
        });
    });

    document.querySelectorAll('.js-eliminar-oferta').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (!window.confirm('¿Eliminar esta oferta? No se podrá reactivar.')) {
                event.preventDefault();
            }
        });
    });

    gestionarOfertas?.addEventListener('click', () => {
        window.history.replaceState(null, '', `${window.location.pathname}${window.location.search}#gestionar-ofertas`);
    });

    modalGestionOfertas?.addEventListener('hidden.bs.modal', () => {
        if (window.location.hash === '#gestionar-ofertas') {
            window.history.replaceState(null, '', window.location.pathname + window.location.search);
        }
    });
});
