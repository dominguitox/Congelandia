document.addEventListener('DOMContentLoaded', () => {

    /*
    =========================================================
    ELEMENTOS
    =========================================================
    */

    const btnNuevaCategoria =
        document.getElementById('btnNuevaCategoria');

    const categoriasList =
        document.getElementById('categoriasList');

    const buscarCategoria =
        document.getElementById('buscarCategoria');

    const searchEmpty =
        document.getElementById('searchEmpty');


    /*
    =========================================================
    PANEL DERECHO
    =========================================================
    */

    const detailPanel =
        document.getElementById('categoriaDetailPanel');

    const btnCerrarDetalle =
        document.getElementById('btnCerrarDetalle');

    const detailCategoriaNombre =
        document.getElementById('detailCategoriaNombre');

    const detailProductos =
        document.getElementById('detailProductos');


    /*
    =========================================================
    MODALES DIALOG
    =========================================================
    */

    const modalNueva =
        document.getElementById('modalNuevaCategoria');

    const modalEditar =
        document.getElementById('modalEditarCategoria');

    const modalEliminar =
        document.getElementById('modalEliminarCategoria');

    const modalError =
        document.getElementById('modalErrorCategoria');


    /*
    =========================================================
    FORMULARIOS
    =========================================================
    */

    const nombreNueva =
        document.getElementById('nombreNuevaCategoria');

    const nombreEditar =
        document.getElementById('nombreEditarCategoria');

    const formEditar =
        document.getElementById('formEditarCategoria');

    const formEliminar =
        document.getElementById('formEliminarCategoria');


    /*
    =========================================================
    OTROS
    =========================================================
    */

    const eliminarMensaje =
        document.getElementById('eliminarMensaje');

    const errorCategoriaNombre =
        document.getElementById('errorCategoriaNombre');


    /*
    =========================================================
    FUNCIONES MODAL
    =========================================================
    */

    function abrirDialog(dialog) {

        if (!dialog) {
            return;
        }

        /*
         * Por seguridad, cerramos cualquier otro dialog abierto.
         */
        document
            .querySelectorAll('dialog[open]')
            .forEach((abierto) => {

                if (abierto !== dialog) {
                    abierto.close();
                }

            });


        /*
         * showModal() coloca el dialog en el TOP LAYER
         * del navegador.
         */
        if (!dialog.open) {
            dialog.showModal();
        }


        /*
         * Bloqueamos el scroll de la página.
         */
        document.body.classList.add(
            'dialog-open'
        );

    }


    function cerrarDialog(dialog) {

        if (!dialog) {
            return;
        }

        if (dialog.open) {
            dialog.close();
        }


        /*
         * Si no queda ningún modal abierto,
         * devolvemos el scroll.
         */
        const quedaAbierto =
            document.querySelector(
                'dialog[open]'
            );

        if (!quedaAbierto) {

            document.body.classList.remove(
                'dialog-open'
            );

        }

    }


    /*
    =========================================================
    NUEVA CATEGORÍA
    =========================================================
    */

    if (btnNuevaCategoria) {

        btnNuevaCategoria.addEventListener(
            'click',
            () => {

                if (nombreNueva) {
                    nombreNueva.value = '';
                }

                abrirDialog(modalNueva);

                setTimeout(() => {

                    if (nombreNueva) {
                        nombreNueva.focus();
                    }

                }, 50);

            }
        );

    }


    /*
    =========================================================
    EDITAR
    =========================================================
    */

    function editarCategoria(button) {

        if (!button) {
            return;
        }

        const id =
            button.dataset.id;

        const nombre =
            button.dataset.nombre || '';


        if (!id || !formEditar) {
            return;
        }


        nombreEditar.value =
            nombre;


        formEditar.action =
            `/categorias/${id}`;


        abrirDialog(modalEditar);


        setTimeout(() => {

            nombreEditar.focus();
            nombreEditar.select();

        }, 50);

    }


    /*
    =========================================================
    ELIMINAR
    =========================================================
    */

    function eliminarCategoria(button) {

        if (!button) {
            return;
        }

        const id =
            button.dataset.id;

        const nombre =
            button.dataset.nombre || '';

        const cantidad =
            Number(
                button.dataset.productos || 0
            );


        /*
         * SI TIENE PRODUCTOS
         */

        if (cantidad > 0) {

            if (errorCategoriaNombre) {

                errorCategoriaNombre.textContent =
                    `"${nombre}"`;

            }

            abrirDialog(modalError);

            return;
        }


        /*
         * SI NO TIENE PRODUCTOS
         */

        if (!id || !formEliminar) {
            return;
        }


        formEliminar.action =
            `/categorias/${id}`;


        if (eliminarMensaje) {

            eliminarMensaje.textContent =
                `¿Estás seguro de eliminar la categoría "${nombre}"?`;

        }


        abrirDialog(modalEliminar);

    }


    /*
    =========================================================
    PANEL DE PRODUCTOS
    =========================================================
    */

    function abrirDetalle(button) {

        if (!button || !detailPanel) {
            return;
        }

        const id =
            button.dataset.id;

        const nombre =
            button.dataset.nombre || '';


        const template =
            document.getElementById(
                `productos-${id}`
            );


        if (!template) {
            return;
        }


        /*
         * Quitar selección anterior.
         */

        document
            .querySelectorAll(
                '.categoria-row.selected'
            )
            .forEach((row) => {

                row.classList.remove(
                    'selected'
                );

            });


        /*
         * Seleccionar categoría actual.
         */

        const row =
            button.closest(
                '.categoria-row'
            );

        if (row) {

            row.classList.add(
                'selected'
            );

        }


        /*
         * Nombre.
         */

        detailCategoriaNombre.textContent =
            nombre;


        /*
         * Productos.
         */

        detailProductos.innerHTML =
            template.innerHTML;


        /*
         * Mostrar panel.
         */

        detailPanel.classList.remove(
            'is-hidden'
        );

        detailPanel.classList.add(
            'is-visible'
        );

        detailPanel.setAttribute(
            'aria-hidden',
            'false'
        );

    }


    /*
    =========================================================
    CERRAR PANEL
    =========================================================
    */

    function cerrarDetalle() {

        if (!detailPanel) {
            return;
        }

        detailPanel.classList.remove(
            'is-visible'
        );

        detailPanel.classList.add(
            'is-hidden'
        );

        detailPanel.setAttribute(
            'aria-hidden',
            'true'
        );


        document
            .querySelectorAll(
                '.categoria-row.selected'
            )
            .forEach((row) => {

                row.classList.remove(
                    'selected'
                );

            });

    }


    if (btnCerrarDetalle) {

        btnCerrarDetalle.addEventListener(
            'click',
            cerrarDetalle
        );

    }


    /*
    =========================================================
    BOTONES DE CATEGORÍAS
    =========================================================
    */

    if (categoriasList) {

        categoriasList.addEventListener(
            'click',
            (event) => {

                const button =
                    event.target.closest(
                        'button[data-action]'
                    );


                if (!button) {
                    return;
                }


                const action =
                    button.dataset.action;


                if (action === 'expand') {

                    abrirDetalle(button);

                    return;

                }


                if (action === 'edit') {

                    editarCategoria(button);

                    return;

                }


                if (action === 'delete') {

                    eliminarCategoria(button);

                    return;

                }

            }
        );

    }


    /*
    =========================================================
    CERRAR CON X / CANCELAR / ENTENDIDO
    =========================================================
    */

    document.addEventListener(
        'click',
        (event) => {

            const button =
                event.target.closest(
                    '[data-close-dialog]'
                );


            if (!button) {
                return;
            }


            const dialogId =
                button.dataset.closeDialog;


            const dialog =
                document.getElementById(
                    dialogId
                );


            cerrarDialog(dialog);

        }
    );


    /*
    =========================================================
    CLICK EN BACKDROP
    =========================================================
    */

    document
        .querySelectorAll('dialog')
        .forEach((dialog) => {

            dialog.addEventListener(
                'click',
                (event) => {

                    /*
                     * En <dialog>, si el click ocurre
                     * directamente sobre el elemento,
                     * estamos en el backdrop.
                     */

                    if (
                        event.target === dialog
                    ) {

                        cerrarDialog(dialog);

                    }

                }
            );

        });


    /*
    =========================================================
    EVENTO CLOSE
    =========================================================
    */

    document
        .querySelectorAll('dialog')
        .forEach((dialog) => {

            dialog.addEventListener(
                'close',
                () => {

                    const abierto =
                        document.querySelector(
                            'dialog[open]'
                        );

                    if (!abierto) {

                        document.body.classList.remove(
                            'dialog-open'
                        );

                    }

                }
            );

        });


    /*
    =========================================================
    ESC
    =========================================================
    */

    document.addEventListener(
        'keydown',
        (event) => {

            if (event.key !== 'Escape') {
                return;
            }


            const dialog =
                document.querySelector(
                    'dialog[open]'
                );


            if (dialog) {

                cerrarDialog(dialog);

                return;

            }


            if (
                detailPanel &&
                detailPanel.classList.contains(
                    'is-visible'
                )
            ) {

                cerrarDetalle();

            }

        }
    );


    /*
    =========================================================
    BUSCADOR
    =========================================================
    */

    if (buscarCategoria && categoriasList) {

        buscarCategoria.addEventListener(
            'input',
            () => {

                const texto =
                    buscarCategoria.value
                        .trim()
                        .toLowerCase();


                const rows =
                    categoriasList.querySelectorAll(
                        '.categoria-row'
                    );


                let visibles = 0;


                rows.forEach((row) => {

                    const nombre =
                        (
                            row.dataset.nombre || ''
                        ).toLowerCase();


                    if (
                        nombre.includes(texto)
                    ) {

                        row.classList.remove(
                            'search-hidden'
                        );

                        visibles++;

                    } else {

                        row.classList.add(
                            'search-hidden'
                        );

                    }

                });


                if (searchEmpty) {

                    searchEmpty.classList.toggle(
                        'show',
                        rows.length > 0 &&
                        visibles === 0
                    );

                }

            }
        );

    }


    /*
    =========================================================
    TOAST
    =========================================================
    */

    const serverToast =
        document.getElementById(
            'serverToast'
        );


    if (serverToast) {

        setTimeout(() => {

            serverToast.classList.add(
                'hide'
            );

        }, 3500);

    }

});