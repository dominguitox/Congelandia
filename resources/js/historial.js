document.addEventListener('DOMContentLoaded', () => {

    /*
     * =========================================================
     * ELEMENTOS
     * =========================================================
     */

    const buscador = document.getElementById('buscarMovimiento');
    const filtroTipo = document.getElementById('filtroTipo');
    const filas = document.querySelectorAll('.movimiento-row');
    const detalles = document.querySelectorAll('.detalle-row');
    const sinResultados = document.getElementById('sinResultados');


    /*
     * =========================================================
     * ABRIR / CERRAR DETALLES
     * =========================================================
     */

    const botonesExpandir = document.querySelectorAll('.btn-expand');

    botonesExpandir.forEach((boton) => {

        boton.addEventListener('click', () => {

            const targetId = boton.dataset.target;
            const detalle = document.getElementById(targetId);

            if (!detalle) {
                return;
            }

            const estaAbierto = detalle.classList.contains('detalle-visible');


            /*
             * Cerrar todos los detalles.
             */
            detalles.forEach((fila) => {

                fila.classList.remove('detalle-visible');

            });


            /*
             * Restablecer flechas.
             */
            botonesExpandir.forEach((btn) => {

                btn.classList.remove('abierto');
                btn.setAttribute('aria-expanded', 'false');

            });


            /*
             * Si estaba cerrado, abrirlo.
             */
            if (!estaAbierto) {

                detalle.classList.add('detalle-visible');

                boton.classList.add('abierto');

                boton.setAttribute('aria-expanded', 'true');

            }

        });

    });


    /*
     * =========================================================
     * FILTRAR MOVIMIENTOS
     * =========================================================
     */

    function filtrarMovimientos() {

        const texto = buscador
            ? buscador.value.toLowerCase().trim()
            : '';

        const filtro = filtroTipo
            ? filtroTipo.value
            : 'todos';


        let visibles = 0;


        filas.forEach((fila) => {

            const tipo = fila.dataset.tipo || '';
            const nombreTipo = fila.dataset.nombreTipo || '';
            const textoBusqueda = fila.dataset.search || '';


            /*
             * Comprobar búsqueda.
             */
            const coincideBusqueda =
                texto === '' ||
                textoBusqueda.includes(texto);


            /*
             * Comprobar filtro.
             */
            let coincideTipo = true;


            if (filtro === 'ingreso') {

                coincideTipo = tipo === 'ingreso';

            } else if (filtro.startsWith('salida:')) {

                const tipoSalida = filtro
                    .replace('salida:', '')
                    .trim();

                coincideTipo =
                    tipo === 'salida' &&
                    nombreTipo === tipoSalida;

            }


            const mostrar =
                coincideBusqueda &&
                coincideTipo;


            fila.style.display = mostrar
                ? ''
                : 'none';


            /*
             * Buscar su fila de detalle.
             */
            const boton = fila.querySelector('.btn-expand');

            if (boton) {

                const detalleId = boton.dataset.target;
                const detalle = document.getElementById(detalleId);

                if (detalle) {

                    detalle.style.display = mostrar
                        ? ''
                        : 'none';

                    if (!mostrar) {

                        detalle.classList.remove(
                            'detalle-visible'
                        );

                        boton.classList.remove('abierto');

                        boton.setAttribute(
                            'aria-expanded',
                            'false'
                        );

                    }

                }

            }


            if (mostrar) {

                visibles++;

            }

        });


        /*
         * Mostrar mensaje cuando no existen resultados.
         */
        if (sinResultados) {

            sinResultados.style.display =
                visibles === 0
                    ? ''
                    : 'none';

        }

    }


    /*
     * =========================================================
     * EVENTOS DE BÚSQUEDA
     * =========================================================
     */

    if (buscador) {

        buscador.addEventListener(
            'input',
            filtrarMovimientos
        );

    }


    if (filtroTipo) {

        filtroTipo.addEventListener(
            'change',
            filtrarMovimientos
        );

    }


});