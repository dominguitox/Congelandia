import { Modal, Collapse } from 'bootstrap';

window.editarProducto = function (boton) {
    document.getElementById('edit_codigo').value = boton.dataset.codigo;
    document.getElementById('edit_nombre').value = boton.dataset.nombre;
    document.getElementById('edit_idCategoria').value = boton.dataset.categoria;
    const categoria = document.getElementById('edit_idCategoria');
    const nombreCategoria = categoria.closest('[data-relacion-inventario]').querySelector('[data-relacion-nombre]');
    if (nombreCategoria) nombreCategoria.value = '';
    categoria.dispatchEvent(new Event('change'));
    document.getElementById('edit_precioVenta').value = boton.dataset.precio;
    document.getElementById('edit_descripcion').value = boton.dataset.descripcion;

    document.getElementById('formEditarProducto').action = boton.dataset.updateUrl;
};

document.addEventListener('DOMContentLoaded', () => {
    document.querySelectorAll('[data-relacion-inventario]').forEach((relacion) => {
        const select = relacion.querySelector('[data-relacion-select]');
        const nombre = relacion.querySelector('[data-relacion-nombre]');
        const detalle = document.getElementById(select.getAttribute('aria-controls'));
        const stock = relacion.dataset.stockInput ? document.getElementById(relacion.dataset.stockInput) : null;
        const actualizar = () => {
            const crear = select.value === 'nuevo';
            const habilitado = crear && (!stock || Number(stock.value) > 0);
            if (detalle) detalle.hidden = !crear;
            if (nombre) {
                nombre.disabled = !habilitado;
                nombre.required = habilitado;
            }
        };
        select.addEventListener('change', actualizar);
        stock?.addEventListener('input', actualizar);
        actualizar();
    });

    const abastecimiento = document.getElementById('collapseAbastecimiento');
    abastecimiento?.closest('form').addEventListener('invalid', (event) => {
        if (abastecimiento.contains(event.target) && !abastecimiento.classList.contains('show')) {
            event.preventDefault();
            abastecimiento.addEventListener('shown.bs.collapse', () => event.target.focus(), { once: true });
            Collapse.getOrCreateInstance(abastecimiento, { toggle: false }).show();
        }
    }, true);

    const botonGenerarCodigo = document.getElementById('btnGenerarCodigo');
    const codigoInput = document.getElementById('codigo');
    const codigoEstado = document.getElementById('codigoEstado');

    if (botonGenerarCodigo && codigoInput && codigoEstado) {
        let codigoGenerado = null;

        const anunciarCodigo = (mensaje, tipo = 'info') => {
            codigoEstado.textContent = mensaje;
            codigoEstado.classList.toggle('text-danger', tipo === 'error');
            codigoEstado.classList.toggle('text-success', tipo === 'success');
        };

        codigoInput.addEventListener('input', () => {
            if (codigoGenerado !== null && codigoInput.value !== codigoGenerado) {
                codigoGenerado = null;
                anunciarCodigo('Código editado. Puedes escanear, escribir o generar otro código interno.');
            }
        });

        botonGenerarCodigo.addEventListener('click', async () => {
            const textoOriginal = botonGenerarCodigo.textContent;
            const csrf = document.querySelector('meta[name="csrf-token"]')?.content;

            botonGenerarCodigo.disabled = true;
            botonGenerarCodigo.setAttribute('aria-busy', 'true');
            botonGenerarCodigo.textContent = 'Generando…';
            anunciarCodigo('Generando código interno…');

            try {
                const respuesta = await fetch(botonGenerarCodigo.dataset.generarUrl, {
                    method: 'POST',
                    headers: {
                        Accept: 'application/json',
                        'X-CSRF-TOKEN': csrf,
                    },
                });
                const datos = await respuesta.json().catch(() => ({}));

                if (respuesta.status === 419) {
                    throw new Error('Tu sesión expiró. Recarga la página y vuelve a intentarlo.');
                }
                if (!respuesta.ok || typeof datos.codigo !== 'string') {
                    throw new Error(datos.message || 'No se pudo generar el código. Intenta nuevamente.');
                }

                codigoInput.value = datos.codigo;
                codigoGenerado = datos.codigo;
                anunciarCodigo(datos.mensaje || `Código interno ${datos.formato || 'EAN-13'} generado. Revísalo o edítalo antes de guardar.`, 'success');
                codigoInput.focus();
                codigoInput.setSelectionRange(codigoInput.value.length, codigoInput.value.length);
            } catch (error) {
                anunciarCodigo(error.message || 'No se pudo generar el código. Intenta nuevamente.', 'error');
            } finally {
                botonGenerarCodigo.disabled = false;
                botonGenerarCodigo.removeAttribute('aria-busy');
                botonGenerarCodigo.textContent = textoOriginal;
            }
        });
    }

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

    const formEditar = document.getElementById('formEditarProducto');
    if (formEditar && document.querySelector('[data-modal-error]')?.dataset.modalError === 'modalEditarProducto') {
        formEditar.action = formEditar.dataset.oldAction;
    }

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
            form.action = boton.dataset.updateUrl;
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
