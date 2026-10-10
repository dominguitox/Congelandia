document.addEventListener('DOMContentLoaded', function () {

    // =====================================================
    // ELEMENTOS
    // =====================================================

    const btnNuevo = document.getElementById('btnNuevoProveedor');

    const modalNuevo =
        document.getElementById('modalNuevoProveedor');

    const modalEditar =
        document.getElementById('modalEditarProveedor');

    const modalEliminar =
        document.getElementById('modalEliminarProveedor');

    const formNuevo =
        document.getElementById('formNuevoProveedor');

    const formEditar =
        document.getElementById('formEditarProveedor');

    const formEliminar =
        document.getElementById('formEliminarProveedor');

    const buscar =
        document.getElementById('buscarProveedor');

    const editarNombre =
        document.getElementById('editarNombre');

    const editarTelefono =
        document.getElementById('editarTelefono');

    const editarCorreo =
        document.getElementById('editarCorreo');

    const tituloEliminar =
        document.getElementById('confirmacionTitulo');

    const mensajeEliminar =
        document.getElementById('confirmacionMensaje');

    const btnConfirmarEliminar =
        document.getElementById('btnConfirmarEliminar');


    // =====================================================
    // MODALES
    // =====================================================

    function abrirModal(modal) {

        if (!modal) {
            return;
        }

        modal.classList.add('activo');
        modal.setAttribute('aria-hidden', 'false');

        document.body.classList.add('modal-abierto');
    }


    function cerrarModal(modal) {

        if (!modal) {
            return;
        }

        modal.classList.remove('activo');
        modal.setAttribute('aria-hidden', 'true');

        const abierto =
            document.querySelector('.modal-overlay.activo');

        if (!abierto) {
            document.body.classList.remove('modal-abierto');
        }
    }


    // =====================================================
    // NUEVO PROVEEDOR
    // =====================================================

    if (btnNuevo) {

        btnNuevo.addEventListener('click', function () {

            if (formNuevo) {
                formNuevo.reset();
            }

            abrirModal(modalNuevo);
        });
    }


    // =====================================================
    // CERRAR MODALES
    // =====================================================

    document
        .querySelectorAll('[data-cerrar-modal]')
        .forEach(function (boton) {

            boton.addEventListener('click', function () {

                const id =
                    boton.getAttribute('data-cerrar-modal');

                const modal =
                    document.getElementById(id);

                cerrarModal(modal);
            });
        });


    // =====================================================
    // CERRAR AL HACER CLICK FUERA
    // =====================================================

    document
        .querySelectorAll('.modal-overlay')
        .forEach(function (modal) {

            modal.addEventListener('click', function (event) {

                if (event.target === modal) {
                    cerrarModal(modal);
                }
            });
        });


    // =====================================================
    // ESCAPE
    // =====================================================

    document.addEventListener('keydown', function (event) {

        if (event.key === 'Escape') {

            document
                .querySelectorAll('.modal-overlay.activo')
                .forEach(function (modal) {

                    cerrarModal(modal);
                });
        }
    });


    // =====================================================
    // FLECHA / PRODUCTOS
    // =====================================================

    document
        .querySelectorAll('.btn-expandir')
        .forEach(function (boton) {

            boton.addEventListener('click', function () {

                const fila =
                    boton.closest('.proveedor-row');

                if (!fila) {
                    return;
                }

                const detalle =
                    fila.nextElementSibling;

                if (!detalle) {
                    return;
                }

                const abierto =
                    detalle.classList.contains('visible');


                // Cerrar otros proveedores
                document
                    .querySelectorAll(
                        '.productos-detalle-row.visible'
                    )
                    .forEach(function (otraFila) {

                        otraFila.classList.remove('visible');

                        const otroBoton =
                            otraFila
                                .previousElementSibling
                                ?.querySelector('.btn-expandir');

                        if (otroBoton) {

                            otroBoton.classList.remove(
                                'abierto'
                            );

                            otroBoton.setAttribute(
                                'aria-expanded',
                                'false'
                            );
                        }
                    });


                // Abrir / cerrar actual
                if (abierto) {

                    detalle.classList.remove('visible');

                    boton.classList.remove('abierto');

                    boton.setAttribute(
                        'aria-expanded',
                        'false'
                    );

                } else {

                    detalle.classList.add('visible');

                    boton.classList.add('abierto');

                    boton.setAttribute(
                        'aria-expanded',
                        'true'
                    );
                }
            });
        });


    // =====================================================
    // EDITAR PROVEEDOR
    // =====================================================

    document
        .querySelectorAll('.btn-editar')
        .forEach(function (boton) {

            boton.addEventListener('click', function () {

                const id =
                    boton.getAttribute('data-id');

                const nombre =
                    boton.getAttribute('data-nombre') || '';

                const telefono =
                    boton.getAttribute('data-telefono') || '';

                const correo =
                    boton.getAttribute('data-correo') || '';


                if (editarNombre) {
                    editarNombre.value = nombre;
                }

                if (editarTelefono) {
                    editarTelefono.value = telefono;
                }

                if (editarCorreo) {
                    editarCorreo.value = correo;
                }


                /*
                 * URL de actualización.
                 */
                if (formEditar) {

                    formEditar.action =
                        '/proveedores/' + id;
                }


                abrirModal(modalEditar);
            });
        });


    // =====================================================
    // ELIMINAR PROVEEDOR
    // =====================================================

    document
        .querySelectorAll('.btn-eliminar')
        .forEach(function (boton) {

            boton.addEventListener('click', function () {

                const nombre =
                    boton.getAttribute('data-nombre') ||
                    'este proveedor';

                const productos =
                    parseInt(
                        boton.getAttribute('data-productos') || '0',
                        10
                    );

                const deleteUrl =
                    boton.getAttribute('data-delete-url');


                /*
                 * Tiene productos.
                 */
                if (productos > 0) {

                    tituloEliminar.textContent =
                        'No se puede eliminar';

                    mensajeEliminar.textContent =
                        'El proveedor "' +
                        nombre +
                        '" tiene ' +
                        productos +
                        ' ' +
                        (productos === 1
                            ? 'producto asociado.'
                            : 'productos asociados.') +
                        ' Debes gestionar sus productos antes de eliminarlo.';

                    btnConfirmarEliminar.style.display =
                        'none';

                }

                /*
                 * No tiene productos.
                 */
                else {

                    tituloEliminar.textContent =
                        '¿Eliminar proveedor?';

                    mensajeEliminar.textContent =
                        '¿Estás seguro de que deseas eliminar "' +
                        nombre +
                        '"? Esta acción no se puede deshacer.';

                    btnConfirmarEliminar.style.display =
                        'inline-flex';


                    if (formEliminar) {

                        formEliminar.action =
                            deleteUrl;
                    }
                }


                abrirModal(modalEliminar);
            });
        });


    // =====================================================
    // BUSCADOR
    // =====================================================

    if (buscar) {

        buscar.addEventListener('input', function () {

            const texto =
                buscar.value.trim().toLowerCase();

            let encontrados = 0;


            document
                .querySelectorAll('.proveedor-row')
                .forEach(function (fila) {

                    const nombre =
                        fila.getAttribute('data-nombre') || '';

                    const telefono =
                        fila.getAttribute('data-telefono') || '';

                    const correo =
                        fila.getAttribute('data-correo') || '';


                    const coincide =
                        nombre.includes(texto) ||
                        telefono.includes(texto) ||
                        correo.includes(texto);


                    const detalle =
                        fila.nextElementSibling;


                    if (coincide) {

                        fila.style.display = '';

                        encontrados++;

                    } else {

                        fila.style.display = 'none';

                        if (
                            detalle &&
                            detalle.classList.contains(
                                'productos-detalle-row'
                            )
                        ) {

                            detalle.classList.remove(
                                'visible'
                            );
                        }
                    }
                });


            const sinResultados =
                document.getElementById(
                    'sinResultadosBusqueda'
                );


            if (sinResultados) {

                if (
                    texto !== '' &&
                    encontrados === 0
                ) {

                    sinResultados.classList.remove(
                        'fila-oculta'
                    );

                } else {

                    sinResultados.classList.add(
                        'fila-oculta'
                    );
                }
            }
        });
    }

});