const rutasClientes = document.getElementById('rutasClientes');
const rutaCliente = (nombre, rut) => rutasClientes.dataset[nombre].replace('__RUT__', encodeURIComponent(rut));

/*
 * =========================================================
 * BUSCADOR
 * =========================================================
 */

document.addEventListener('DOMContentLoaded', function () {

    const buscador =
        document.getElementById('buscadorClientes');

    const tablaDeudas =
        document.getElementById('tablaDeudas');

    const tablaPagados =
        document.getElementById('tablaPagados');

    const sinResultadosDeudas =
        document.getElementById('sinResultadosDeudas');

    const sinResultadosPagados =
        document.getElementById('sinResultadosPagados');


    if (!buscador) {
        return;
    }


    buscador.addEventListener('input', function () {

        const texto =
            this.value.toLowerCase().trim();


        if (tablaDeudas) {

            const filas =
                tablaDeudas.querySelectorAll(
                    'tr.fila-cliente'
                );

            let encontrados = 0;


            filas.forEach(function (fila) {

                const nombre =
                    fila.querySelector(
                        '.cliente-nombre'
                    )
                    ?.textContent
                    .toLowerCase()
                    .trim() || '';


                const telefono =
                    fila.querySelector(
                        '.cliente-telefono'
                    )
                    ?.textContent
                    .toLowerCase()
                    .trim() || '';


                const coincide =
                    nombre.includes(texto) ||
                    telefono.includes(texto);


                fila.style.display =
                    coincide ? '' : 'none';


                if (coincide) {
                    encontrados++;
                }

            });


            if (sinResultadosDeudas) {

                sinResultadosDeudas.style.display =
                    texto !== '' && encontrados === 0
                        ? ''
                        : 'none';

            }

        }


        if (tablaPagados) {

            const filas =
                tablaPagados.querySelectorAll(
                    'tr.fila-cliente'
                );

            let encontrados = 0;


            filas.forEach(function (fila) {

                const nombre =
                    fila.querySelector(
                        '.cliente-nombre'
                    )
                    ?.textContent
                    .toLowerCase()
                    .trim() || '';


                const telefono =
                    fila.querySelector(
                        '.cliente-telefono'
                    )
                    ?.textContent
                    .toLowerCase()
                    .trim() || '';


                const coincide =
                    nombre.includes(texto) ||
                    telefono.includes(texto);


                fila.style.display =
                    coincide ? '' : 'none';


                if (coincide) {
                    encontrados++;
                }

            });


            if (sinResultadosPagados) {

                sinResultadosPagados.style.display =
                    texto !== '' && encontrados === 0
                        ? ''
                        : 'none';

            }

        }

    });

});


/*
 * =========================================================
 * FORMULARIO NUEVO CLIENTE
 * =========================================================
 */

function toggleFormularioCliente()
{
    const formulario =
        document.getElementById(
            'formularioCliente'
        );


    if (!formulario) {
        return;
    }


    if (
        formulario.style.display === 'none' ||
        formulario.style.display === ''
    ) {

        formulario.style.display = 'block';

    } else {

        formulario.style.display = 'none';

    }
}


/*
 * =========================================================
 * CONFIRMAR ELIMINACIÓN
 * =========================================================
 */

function confirmarEliminacionCliente(nombre)
{
    return confirm(
        '¿Está seguro de eliminar al cliente "' +
        nombre +
        '"?\n\n' +
        'El cliente dejará de aparecer en el listado, ' +
        'pero su historial de ventas se conservará.'
    );
}


/*
 * =========================================================
 * ABRIR MODAL EDITAR CLIENTE
 * =========================================================
 */

function abrirModalEditar(
    rutCliente,
    nombre,
    telefono
)
{
    const modal =
        document.getElementById(
            'modalEditar'
        );

    const formulario =
        document.getElementById(
            'formEditarCliente'
        );

    const rut =
        document.getElementById(
            'editarRut'
        );

    const nombreInput =
        document.getElementById(
            'editarNombre'
        );

    const telefonoInput =
        document.getElementById(
            'editarTelefono'
        );


    /*
     * Mostrar datos actuales.
     */

    rut.value =
        rutCliente;

    nombreInput.value =
        nombre || '';

    telefonoInput.value =
        telefono || '';


    /*
     * Generar URL de actualización.
     */

    formulario.action =
        rutaCliente('update', rutCliente);


    /*
     * Mostrar modal.
     */

    modal.style.display =
        'flex';


    /*
     * Enfocar nombre.
     */

    setTimeout(function () {

        nombreInput.focus();

    }, 100);
}


/*
 * =========================================================
 * CERRAR MODAL EDITAR
 * =========================================================
 */

function cerrarModalEditar()
{
    const modal =
        document.getElementById(
            'modalEditar'
        );


    if (modal) {

        modal.style.display =
            'none';

    }
}


/*
 * =========================================================
 * HISTORIAL
 * =========================================================
 */

async function verHistorialCliente(rutCliente)
{
    const modal =
        document.getElementById(
            'modalHistorial'
        );

    const nombre =
        document.getElementById(
            'historialClienteNombre'
        );

    const rut =
        document.getElementById(
            'historialClienteRut'
        );

    const telefono =
        document.getElementById(
            'historialClienteTelefono'
        );

    const cantidadVentas =
        document.getElementById(
            'historialCantidadVentas'
        );

    const contenido =
        document.getElementById(
            'historialContenido'
        );


    modal.style.display =
        'flex';


    nombre.textContent =
        'Cargando...';

    rut.textContent =
        '-';

    telefono.textContent =
        '-';

    cantidadVentas.textContent =
        '0';


    contenido.innerHTML = `
        <p class="historial-cargando">
            Cargando historial...
        </p>
    `;


    try {

        const response =
            await fetch(
                rutaCliente('historial', rutCliente)
            );


        if (!response.ok) {

            throw new Error(
                'No fue posible obtener el historial.'
            );

        }


        const data =
            await response.json();


        nombre.textContent =
            data.cliente.nombre;

        rut.textContent =
            data.cliente.rutCliente;

        telefono.textContent =
            data.cliente.telefono ||
            'Sin teléfono';

        cantidadVentas.textContent =
            data.ventas.length;


        if (data.ventas.length === 0) {

            contenido.innerHTML = `
                <div class="historial-vacio">
                    Este cliente no tiene ventas registradas.
                </div>
            `;

            return;

        }


        contenido.innerHTML =
            data.ventas.map(function (venta) {

                const fecha =
                    new Date(venta.fecha)
                    .toLocaleDateString('es-CL');


                const total =
                    Number(venta.total)
                    .toLocaleString('es-CL');


                const pagado =
                    Number(venta.totalPagado)
                    .toLocaleString('es-CL');


                const saldo =
                    Number(venta.saldoPendiente)
                    .toLocaleString('es-CL');


                let claseEstado =
                    'estado-pagada';


                if (
                    venta.estado === 'Pendiente'
                ) {

                    claseEstado =
                        'estado-pendiente';

                } else if (
                    venta.estado === 'Pago parcial'
                ) {

                    claseEstado =
                        'estado-parcial';

                }


                let detallesHTML = '';


                if (
                    venta.detalles &&
                    venta.detalles.length > 0
                ) {

                    detallesHTML =
                        venta.detalles.map(
                            function (detalle) {

                                const precio =
                                    Number(
                                        detalle.precioCobrado
                                    )
                                    .toLocaleString('es-CL');


                                return `
                                    <div class="historial-producto">

                                        <span>
                                            ${detalle.codigoProducto}
                                        </span>

                                        <span>
                                            x${detalle.cantidad}
                                        </span>

                                        <strong>
                                            $${precio}
                                        </strong>

                                    </div>
                                `;

                            }
                        ).join('');

                } else {

                    detallesHTML = `
                        <p class="sin-detalles">
                            Sin detalle disponible
                        </p>
                    `;

                }


                return `
                    <div class="historial-venta">

                        <div class="historial-venta-header">

                            <div>

                                <strong>
                                    Venta #${venta.idSalida}
                                </strong>

                                <span>
                                    ${fecha}
                                </span>

                            </div>

                            <span class="${claseEstado}">
                                ${venta.estado}
                            </span>

                        </div>


                        <div class="historial-montos">

                            <div>

                                <span>
                                    Total
                                </span>

                                <strong>
                                    $${total}
                                </strong>

                            </div>


                            <div>

                                <span>
                                    Pagado
                                </span>

                                <strong class="monto-pagado">
                                    $${pagado}
                                </strong>

                            </div>


                            <div>

                                <span>
                                    Saldo
                                </span>

                                <strong class="monto-saldo">
                                    $${saldo}
                                </strong>

                            </div>

                        </div>


                        <div class="historial-productos">

                            <h5>
                                Productos
                            </h5>

                            ${detallesHTML}

                        </div>

                    </div>
                `;

            }).join('');


    } catch (error) {

        console.error(error);

        contenido.innerHTML = `
            <div class="historial-error">
                No fue posible cargar el historial del cliente.
            </div>
        `;

    }
}


/*
 * =========================================================
 * CERRAR HISTORIAL
 * =========================================================
 */

function cerrarHistorial()
{
    const modal =
        document.getElementById(
            'modalHistorial'
        );


    if (modal) {

        modal.style.display =
            'none';

    }
}


/*
 * =========================================================
 * ABRIR MODAL DE PAGO
 * =========================================================
 */

async function abrirModalPago(rutCliente)
{
    const modal =
        document.getElementById(
            'modalPago'
        );

    const selectVenta =
        document.getElementById(
            'pagoVenta'
        );

    const nombreCliente =
        document.getElementById(
            'pagoClienteNombre'
        );

    const resumen =
        document.getElementById(
            'pagoResumen'
        );

    const error =
        document.getElementById(
            'pagoError'
        );


    modal.style.display =
        'flex';


    selectVenta.innerHTML = `
        <option value="">
            Cargando ventas...
        </option>
    `;

    selectVenta.disabled =
        true;

    resumen.style.display =
        'none';

    error.style.display =
        'none';

    error.textContent =
        '';

    document.getElementById(
        'montoPagado'
    ).value =
        '';


    try {

        const response =
            await fetch(
                rutaCliente('historial', rutCliente)
            );


        if (!response.ok) {

            throw new Error(
                'No fue posible obtener las ventas del cliente.'
            );

        }


        const data =
            await response.json();


        nombreCliente.textContent =
            data.cliente.nombre;


        const ventasPendientes =
            data.ventas.filter(function (venta) {

                return Number(
                    venta.saldoPendiente
                ) > 0;

            });


        if (ventasPendientes.length === 0) {

            selectVenta.innerHTML = `
                <option value="">
                    Este cliente no tiene deudas pendientes
                </option>
            `;

            selectVenta.disabled =
                true;

            error.textContent =
                'No existen ventas con saldo pendiente para este cliente.';

            error.style.display =
                'block';

            return;

        }


        selectVenta.innerHTML = `
            <option value="">
                Seleccione una venta
            </option>
        `;


        ventasPendientes.forEach(function (venta) {

            const option =
                document.createElement('option');


            option.value =
                venta.idSalida;


            option.textContent =
                `Venta #${venta.idSalida} - ${formatearMoneda(venta.saldoPendiente)} pendiente`;


            option.dataset.total =
                venta.total;


            option.dataset.pagado =
                venta.totalPagado;


            option.dataset.saldo =
                venta.saldoPendiente;


            selectVenta.appendChild(option);

        });


        selectVenta.disabled =
            false;


        selectVenta.onchange =
            function () {

                actualizarResumenPago();

            };


    } catch (errorFetch) {

        console.error(errorFetch);

        selectVenta.innerHTML = `
            <option value="">
                Error al cargar las ventas
            </option>
        `;

        selectVenta.disabled =
            true;

        error.textContent =
            'No fue posible cargar las ventas del cliente.';

        error.style.display =
            'block';
    }
}


/*
 * =========================================================
 * ACTUALIZAR RESUMEN PAGO
 * =========================================================
 */

function actualizarResumenPago()
{
    const select =
        document.getElementById(
            'pagoVenta'
        );

    const option =
        select.options[
            select.selectedIndex
        ];

    const resumen =
        document.getElementById(
            'pagoResumen'
        );

    const monto =
        document.getElementById(
            'montoPagado'
        );


    if (
        !option ||
        !option.value
    ) {

        resumen.style.display =
            'none';

        monto.removeAttribute(
            'max'
        );

        return;
    }


    const total =
        Number(
            option.dataset.total || 0
        );

    const pagado =
        Number(
            option.dataset.pagado || 0
        );

    const saldo =
        Number(
            option.dataset.saldo || 0
        );


    document.getElementById(
        'pagoTotalVenta'
    ).textContent =
        formatearMoneda(total);


    document.getElementById(
        'pagoTotalPagado'
    ).textContent =
        formatearMoneda(pagado);


    document.getElementById(
        'pagoSaldoPendiente'
    ).textContent =
        formatearMoneda(saldo);


    monto.max =
        saldo;

    monto.value =
        '';


    resumen.style.display =
        'grid';
}


/*
 * =========================================================
 * FORMATEAR MONEDA
 * =========================================================
 */

function formatearMoneda(valor)
{
    return '$' +
        Number(valor || 0)
        .toLocaleString('es-CL');
}


/*
 * =========================================================
 * VALIDAR PAGO
 * =========================================================
 */

document.addEventListener(
    'DOMContentLoaded',
    function () {

        const form =
            document.getElementById(
                'formRegistrarPago'
            );


        if (!form) {
            return;
        }


        form.addEventListener(
            'submit',
            function (event) {

                const select =
                    document.getElementById(
                        'pagoVenta'
                    );

                const monto =
                    Number(
                        document.getElementById(
                            'montoPagado'
                        ).value
                    );


                if (!select.value) {

                    event.preventDefault();

                    mostrarErrorPago(
                        'Debe seleccionar una venta.'
                    );

                    return;
                }


                const option =
                    select.options[
                        select.selectedIndex
                    ];


                const saldo =
                    Number(
                        option.dataset.saldo || 0
                    );


                if (monto <= 0) {

                    event.preventDefault();

                    mostrarErrorPago(
                        'El monto debe ser mayor que cero.'
                    );

                    return;
                }


                if (monto > saldo) {

                    event.preventDefault();

                    mostrarErrorPago(
                        'El monto no puede superar el saldo pendiente.'
                    );

                    return;
                }


                const metodo =
                    document.querySelector(
                        'input[name="idMetodo"]:checked'
                    );


                if (!metodo) {

                    event.preventDefault();

                    mostrarErrorPago(
                        'Debe seleccionar un método de pago.'
                    );

                    return;
                }

                form.action = form.dataset.actionTemplate.replace(
                    '__ID_SALIDA__',
                    encodeURIComponent(select.value)
                );

            }
        );

    }
);


/*
 * =========================================================
 * MOSTRAR ERROR PAGO
 * =========================================================
 */

function mostrarErrorPago(mensaje)
{
    const error =
        document.getElementById(
            'pagoError'
        );


    error.textContent =
        mensaje;

    error.style.display =
        'block';
}


/*
 * =========================================================
 * CERRAR MODAL PAGO
 * =========================================================
 */

function cerrarModalPago()
{
    const modal =
        document.getElementById(
            'modalPago'
        );


    modal.style.display =
        'none';


    const form =
        document.getElementById(
            'formRegistrarPago'
        );


    if (form) {
        form.reset();
    }


    document.getElementById(
        'pagoResumen'
    ).style.display =
        'none';


    document.getElementById(
        'pagoError'
    ).style.display =
        'none';
}


/*
 * =========================================================
 * CERRAR MODALES CON ESC
 * =========================================================
 */

document.addEventListener(
    'keydown',
    function (event) {

        if (event.key === 'Escape') {

            cerrarHistorial();

            cerrarModalPago();

            cerrarModalEditar();

        }

    }
);


/*
 * =========================================================
 * CERRAR HISTORIAL AL HACER CLICK FUERA
 * =========================================================
 */

document.addEventListener(
    'click',
    function (event) {

        const modal =
            document.getElementById(
                'modalHistorial'
            );


        if (
            event.target === modal
        ) {

            cerrarHistorial();

        }

    }
);


/*
 * =========================================================
 * CERRAR PAGO AL HACER CLICK FUERA
 * =========================================================
 */

document.addEventListener(
    'click',
    function (event) {

        const modal =
            document.getElementById(
                'modalPago'
            );


        if (
            event.target === modal
        ) {

            cerrarModalPago();

        }

    }
);


/*
 * =========================================================
 * CERRAR EDITAR AL HACER CLICK FUERA
 * =========================================================
 */

document.addEventListener(
    'click',
    function (event) {

        const modal =
            document.getElementById(
                'modalEditar'
            );


        if (
            event.target === modal
        ) {

            cerrarModalEditar();

        }

    }
);

// Los botones Blade existentes invocan estos handlers por nombre.
Object.assign(window, { toggleFormularioCliente, confirmarEliminacionCliente, abrirModalEditar, cerrarModalEditar, verHistorialCliente, cerrarHistorial, abrirModalPago, actualizarResumenPago, formatearMoneda, mostrarErrorPago, cerrarModalPago });
