import { Modal } from 'bootstrap';
import { calcularCobro } from './cobro.js';

// 1. Cargamos el carrito desde localStorage al iniciar, o iniciamos un arreglo vacío
let carrito = JSON.parse(localStorage.getItem('carrito')) || [];
const productosFijadosKey = 'pos:productos-fijados';
let productosFijados = [];
let ordenOriginalProductos = new Map();

// Al cargar la página, dibujamos inmediatamente los productos si existen en caché
document.addEventListener("DOMContentLoaded", () => {
    sincronizarPreciosDelCatalogo();
    actualizarTicketVenta();

    const tarjetas = [...document.querySelectorAll('.producto-card')];
    const codigosDisponibles = new Set(tarjetas.map((card) => card.dataset.id));
    ordenOriginalProductos = new Map(tarjetas.map((card, indice) => [card.dataset.id, indice]));

    try {
        const guardados = JSON.parse(localStorage.getItem(productosFijadosKey) || '[]');
        productosFijados = Array.isArray(guardados)
            ? guardados.filter((codigo, indice) => codigosDisponibles.has(codigo) && guardados.indexOf(codigo) === indice)
            : [];
    } catch (error) {
        productosFijados = [];
    }

    tarjetas.forEach((card) => {
        card.addEventListener('click', () => agregarAlCarrito(card));
        card.addEventListener('keydown', (event) => {
            if (event.target !== card) return;
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                agregarAlCarrito(card);
            }
        });

        const botonFijar = card.querySelector('.producto-card-fijar');
        botonFijar?.addEventListener('click', (event) => {
            event.stopPropagation();
            alternarProductoFijado(card);
        });
    });

    tarjetas.forEach((card) => actualizarControlFijado(card, productosFijados.includes(card.dataset.id)));
    guardarProductosFijados();
    ordenarProductosFijados();
    iniciarCobro();
});

function guardarProductosFijados() {
    try {
        localStorage.setItem(productosFijadosKey, JSON.stringify(productosFijados));
    } catch (error) {
        console.warn('No fue posible guardar los productos fijados en este navegador.', error);
    }
}

function ordenarProductosFijados() {
    const grilla = document.getElementById('grillaProductos');
    if (!grilla) return;

    const tarjetas = [...grilla.querySelectorAll('.producto-card')];
    const posicionesFijadas = new Map(productosFijados.map((codigo, indice) => [codigo, indice]));

    tarjetas.sort((a, b) => {
        const posicionA = posicionesFijadas.get(a.dataset.id);
        const posicionB = posicionesFijadas.get(b.dataset.id);
        if (posicionA !== undefined && posicionB !== undefined) return posicionA - posicionB;
        if (posicionA !== undefined) return -1;
        if (posicionB !== undefined) return 1;
        return ordenOriginalProductos.get(a.dataset.id) - ordenOriginalProductos.get(b.dataset.id);
    });

    tarjetas.forEach((card) => grilla.appendChild(card));
}

function alternarProductoFijado(card) {
    const codigo = card.dataset.id;
    const boton = card.querySelector('.producto-card-fijar');
    const indice = productosFijados.indexOf(codigo);
    const fijado = indice === -1;

    if (fijado) {
        productosFijados.push(codigo);
    } else {
        productosFijados.splice(indice, 1);
    }

    actualizarControlFijado(card, fijado);
    guardarProductosFijados();
    ordenarProductosFijados();
    boton?.focus();
}

function actualizarControlFijado(card, fijado) {
    const boton = card.querySelector('.producto-card-fijar');
    if (!boton) return;

    const nombre = boton.dataset.nombreProducto || card.dataset.nombre;
    boton.setAttribute('aria-pressed', String(fijado));
    boton.setAttribute('aria-label', `${fijado ? 'Desfijar' : 'Fijar'} ${nombre} ${fijado ? 'del' : 'al'} inicio del listado`);
    boton.title = fijado ? 'Desfijar producto' : 'Fijar producto';
    card.classList.toggle('producto-card--fijado', fijado);
}

// Función auxiliar para guardar el estado actual en localStorage y actualizar la vista
function guardarYActualizar() {
    localStorage.setItem('carrito', JSON.stringify(carrito));
    actualizarTicketVenta();
}

function formatoCLP(valor) {
    return new Intl.NumberFormat('es-CL', { maximumFractionDigits: 0 }).format(Number(valor) || 0);
}

function escaparHTML(valor) {
    return String(valor).replace(/[&<>"']/g, (caracter) => ({
        '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;'
    })[caracter]);
}

function sincronizarPreciosDelCatalogo() {
    document.querySelectorAll('.producto-card').forEach((card) => {
        const item = carrito.find((producto) => producto.id === card.dataset.id);
        if (!item) return;

        item.nombre = card.dataset.nombre;
        item.precioNormal = Number(card.dataset.precioNormal) || 0;
        item.descuento = Number(card.dataset.descuento) || 0;
        item.precio = Number(card.dataset.precioOferta) || 0;
        item.stock = Number(card.dataset.stock) || 0;
    });
    localStorage.setItem('carrito', JSON.stringify(carrito));
}

window.agregarAlCarrito = function (card) {
    if (cobroPendiente) return;
    const idProducto = card.dataset.id;
    const nombre = card.dataset.nombre;
    const precioNormal = Number(card.dataset.precioNormal) || 0;
    const precioOferta = Number(card.dataset.precioOferta) || 0;
    const descuento = Number(card.dataset.descuento) || 0;
    const stockDisponible = Number(card.dataset.stock) || 0;

    if (stockDisponible < 1) {
        alert(`${nombre} no tiene stock disponible.`);
        return;
    }

    let indice = carrito.findIndex(item => item.id === idProducto);

    if (indice !== -1) {
        carrito[indice].precioNormal = precioNormal;
        carrito[indice].precio = precioOferta;
        carrito[indice].descuento = descuento;
        carrito[indice].stock = stockDisponible;
        if (carrito[indice].cantidad < stockDisponible) {
            carrito[indice].cantidad++;
        } else {
            alert("Stock insuficiente para " + nombre);
            return;
        }
    } else {
        carrito.push({
            id: idProducto,
            nombre: nombre,
            precioNormal: precioNormal,
            precio: precioOferta,
            descuento: descuento,
            stock: stockDisponible,
            cantidad: 1
        });
    }

    guardarYActualizar(); // Guarda en caché y actualiza la interfaz
}


function actualizarTicketVenta() {
    let contenedorTicket = document.getElementById('ticket-items');
    let spanTotal = document.getElementById('ticket-total');
    let spanTotalNormal = document.getElementById('ticket-total-normal');
    let totalNormalWrap = document.getElementById('ticket-total-normal-wrap');
    let totalLabel = document.getElementById('ticket-total-label');

    if (!contenedorTicket) return;

    contenedorTicket.innerHTML = '';
    let totalFinal = 0;
    let totalSinOfertas = 0;
    let hayOfertas = false;

    if (carrito.length === 0) {
        contenedorTicket.innerHTML = `<p class="text-center text-muted mt-5">El carrito está vacío</p>`;
    } else {
        carrito.forEach((item, indice) => {
            let subtotal = item.precio * item.cantidad;
            totalFinal += subtotal;
            totalSinOfertas += (Number(item.precioNormal) || item.precio) * item.cantidad;
            hayOfertas = hayOfertas || Number(item.descuento) > 0;

            const precioHTML = Number(item.descuento) > 0
                ? `<small class="ticket-precio-normal">Sin oferta: $${formatoCLP(item.precioNormal)} c/u</small>
                   <small class="ticket-precio-oferta">Con oferta: $${formatoCLP(item.precio)} c/u</small>`
                : `<small class="text-muted">$${formatoCLP(item.precio)} c/u</small>`;

            let fila = `
                <div class="ticket-linea">
                    <div class="ticket-linea-producto">
                        <h6 class="mb-1">${escaparHTML(item.nombre)}</h6>
                        ${precioHTML}
                    </div>
                    <div class="d-flex align-items-center">
                        <button class="btn btn-sm btn-outline-secondary px-2 me-1" onclick="restarAlCarrito(${indice})">-</button>
                        <span class="badge bg-secondary me-1">Cant. ${item.cantidad}</span>
                        <button class="btn btn-sm btn-outline-secondary px-2 me-2" onclick="sumarAlCarrito(${indice})">+</button>
                        <strong class="me-2">$${formatoCLP(subtotal)}</strong>
                        <button class="btn btn-sm btn-danger" onclick="eliminarDelCarrito(${indice})">&times;</button>
                    </div>
                </div>
            `;
            contenedorTicket.innerHTML += fila;
        });
    }

    if (spanTotal) {
        spanTotal.innerText = `$${formatoCLP(totalFinal)}`;
    }
    if (spanTotalNormal) {
        spanTotalNormal.innerText = `$${formatoCLP(totalSinOfertas)}`;
    }
    if (totalNormalWrap) {
        totalNormalWrap.hidden = !hayOfertas || carrito.length === 0;
    }
    if (totalLabel) {
        totalLabel.innerText = hayOfertas && carrito.length > 0 ? 'Total con ofertas' : 'Total';
    }

    document.querySelectorAll('.producto-card').forEach(card => {
        let idProd = card.getAttribute('data-id');

        // Capturamos los badges correspondientes a este producto específico
        let badgeCarrito = document.getElementById('badge-carrito-' + idProd);
        let badgeStock = document.getElementById('badge-stock-' + idProd);

        if (badgeCarrito && badgeStock) {
            let stockInicial = parseInt(badgeStock.getAttribute('data-stock-inicial'));

            // Buscamos si este producto está actualmente en el arreglo del carrito
            let itemEnCarrito = carrito.find(i => i.id === idProd);
            let cantidadSeleccionada = itemEnCarrito ? itemEnCarrito.cantidad : 0;

            // Actualizamos los textos visuales
            badgeCarrito.innerText = "En carrito: " + cantidadSeleccionada;
            badgeCarrito.hidden = cantidadSeleccionada === 0;
            badgeStock.innerText = "Stock: " + (stockInicial - cantidadSeleccionada);
        }
    });
}


window.eliminarDelCarrito = function (indice) {
    if (cobroPendiente) return;
    carrito.splice(indice, 1);
    guardarYActualizar();
}

window.sumarAlCarrito = function (indice) {
    if (cobroPendiente) return;
    if (carrito[indice].cantidad < carrito[indice].stock) {
        carrito[indice].cantidad++;
        guardarYActualizar();
    } else {
        alert("Stock insuficiente para " + carrito[indice].nombre);
    }
}

window.restarAlCarrito = function (indice) {
    if (cobroPendiente) return;
    if (carrito[indice].cantidad > 1) {
        carrito[indice].cantidad--;
        guardarYActualizar();
    } else {
        eliminarDelCarrito(indice);
    }
}
let cobroPendiente = null;
let enviandoCobro = false;
let numeroPago = 0;
let modalCobro;
let clavePendiente;
let errorServidorCobro = '';

function totalCarrito() {
    return carrito.reduce((total, item) => total + item.cantidad * item.precio, 0);
}

function filasPago() {
    return [...document.querySelectorAll('#pagos-cobro .cobro-pago')];
}

function leerPagos() {
    return filasPago().map(fila => {
        const metodo = fila.querySelector('[data-metodo]');
        return {
            idMetodo: Number(metodo.value),
            monto: Number(fila.querySelector('[data-monto]').value),
            esEfectivo: metodo.selectedOptions[0]?.dataset.efectivo === '1',
        };
    });
}

function mostrarResumenCobro() {
    const resumen = calcularCobro(totalCarrito(), leerPagos(), Number(document.getElementById('efectivo-recibido').value));
    document.getElementById('efectivo-panel').hidden = resumen.efectivo <= 0;
    for (const [id, valor] of Object.entries({asignado: resumen.asignado, restante: resumen.restante, efectivo: resumen.efectivo, vuelto: resumen.vuelto})) {
        document.getElementById('cobro-' + id).textContent = '$' + formatoCLP(valor);
    }
    if (!enviandoCobro && !cobroPendiente) document.getElementById('error-cobro').textContent = errorServidorCobro || resumen.error;
    document.getElementById('confirmar-cobro').disabled = enviandoCobro || (!cobroPendiente && !resumen.valido);
    document.getElementById('agregar-pago').disabled = filasPago().length >= document.querySelector('#plantilla-pago').content.querySelectorAll('option').length;
    filasPago().forEach(fila => fila.querySelector('[data-quitar]').hidden = filasPago().length === 1);
    return resumen;
}

function agregarFilaPago(idMetodo, monto = 0) {
    const fila = document.querySelector('#plantilla-pago').content.firstElementChild.cloneNode(true);
    const metodo = fila.querySelector('[data-metodo]');
    const importe = fila.querySelector('[data-monto]');
    numeroPago++;
    metodo.id = 'cobro-metodo-' + numeroPago;
    importe.id = 'cobro-monto-' + numeroPago;
    fila.querySelector('[data-label-metodo]').htmlFor = metodo.id;
    fila.querySelector('[data-label-monto]').htmlFor = importe.id;
    if (idMetodo) metodo.value = String(idMetodo);
    importe.value = monto;
    fila.querySelector('[data-quitar]').addEventListener('click', () => {
        fila.remove();
        mostrarResumenCobro();
    });
    fila.querySelector('[data-restante]').addEventListener('click', () => {
        const otros = filasPago().filter(f => f !== fila).reduce((suma, f) => suma + Number(f.querySelector('[data-monto]').value), 0);
        importe.value = Math.max(0, totalCarrito() - otros);
        mostrarResumenCobro();
    });
    document.getElementById('pagos-cobro').append(fila);
}

function bloquearCobro() {
    document.getElementById('datos-cobro').disabled = !!cobroPendiente || enviandoCobro;
    document.getElementById('btn-cobrar').disabled = enviandoCobro;
    document.querySelectorAll('#ticket-items button').forEach(boton => boton.disabled = !!cobroPendiente);
    document.querySelectorAll('.producto-card').forEach(card => card.setAttribute('aria-disabled', String(!!cobroPendiente)));
    const confirmar = document.getElementById('confirmar-cobro');
    confirmar.textContent = enviandoCobro ? 'Registrando…' : (cobroPendiente ? 'Reintentar el mismo cobro' : 'Confirmar y registrar venta');
    mostrarResumenCobro();
}

function abrirConfirmacion() {
    if (!carrito.length) {
        document.getElementById('resultado-venta').textContent = 'Agrega productos antes de cobrar.';
        return;
    }
    document.getElementById('resumen-productos').innerHTML = carrito.map(item =>
        '<li>' + item.cantidad + ' × ' + escaparHTML(item.nombre) + ' — $' + formatoCLP(item.cantidad * item.precio) + '</li>'
    ).join('');
    document.getElementById('confirmacion-total').textContent = '$' + formatoCLP(totalCarrito());
    if (!cobroPendiente) {
        errorServidorCobro = '';
        document.getElementById('pagos-cobro').replaceChildren();
        agregarFilaPago(null, totalCarrito());
        document.getElementById('efectivo-recibido').value = '';
        document.getElementById('error-cobro').textContent = '';
    }
    bloquearCobro();
    modalCobro.show();
}

async function confirmarVenta(event) {
    event.preventDefault();
    if (enviandoCobro) return;
    const form = document.getElementById('form-confirmar-venta');
    if (!cobroPendiente && (!form.reportValidity() || !mostrarResumenCobro().valido)) return;
    if (!cobroPendiente) {
        const resumen = mostrarResumenCobro();
        cobroPendiente = {
            carrito: structuredClone(carrito),
            solicitud: {
                idTipo: Number(document.getElementById('rutasPos').dataset.tipoVenta),
                rutCliente: document.getElementById('cliente_select').value || null,
                productos: carrito.map(({id, cantidad, precio}) => ({id, cantidad, precio})),
                pagos: leerPagos().map(({idMetodo, monto}) => ({idMetodo, monto})),
                efectivoRecibido: resumen.efectivo > 0 ? Number(document.getElementById('efectivo-recibido').value) : null,
                claveOperacion: crypto.randomUUID(),
            },
        };
        localStorage.setItem(clavePendiente, JSON.stringify(cobroPendiente));
    }
    enviandoCobro = true;
    errorServidorCobro = '';
    document.getElementById('error-cobro').textContent = '';
    bloquearCobro();
    try {
        const response = await fetch(document.getElementById('rutasPos').dataset.registrarVenta, {
            method: 'POST',
            signal: AbortSignal.timeout(30000),
            headers: {'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content},
            body: JSON.stringify(cobroPendiente.solicitud),
        });
        const data = await response.json();
        if (!response.ok || !data.success) {
            if (response.status >= 400 && response.status < 500 && data.codigo !== 'operacion_utilizada') {
                cobroPendiente = null;
                localStorage.removeItem(clavePendiente);
            }
            throw new Error(Object.values(data.errors || {}).flat().join(' ') || data.error || data.message || 'No se pudo confirmar el cobro.');
        }
        carrito.forEach(item => {
            const card = [...document.querySelectorAll('.producto-card')].find(c => c.dataset.id === item.id);
            const badge = document.getElementById('badge-stock-' + item.id);
            if (card && badge) {
                const stock = Number(badge.dataset.stockInicial) - item.cantidad;
                badge.dataset.stockInicial = String(stock);
                card.dataset.stock = String(stock);
            }
        });
        cobroPendiente = null;
        localStorage.removeItem(clavePendiente);
        carrito = [];
        localStorage.removeItem('carrito');
        actualizarTicketVenta();
        document.getElementById('cliente_select').value = '';
        enviandoCobro = false;
        modalCobro.hide();
        document.getElementById('resultado-venta').textContent = 'Venta #' + data.idSalida + ' registrada. Vuelto a entregar: $' + formatoCLP(data.vuelto) + '.';
    } catch (error) {
        errorServidorCobro = cobroPendiente
            ? error.message + ' Reintenta el mismo cobro para comprobar su resultado; los datos quedan conservados.'
            : error.message;
        document.getElementById('error-cobro').textContent = errorServidorCobro;
    } finally {
        enviandoCobro = false;
        bloquearCobro();
    }
}

function iniciarCobro() {
    const elemento = document.getElementById('modalConfirmarVenta');
    modalCobro = Modal.getOrCreateInstance(elemento);
    clavePendiente = 'cobroPendiente:' + document.getElementById('rutasPos').dataset.usuario;
    elemento.addEventListener('hide.bs.modal', event => { if (enviandoCobro) event.preventDefault(); });
    document.getElementById('btn-cobrar').addEventListener('click', abrirConfirmacion);
    document.getElementById('form-confirmar-venta').addEventListener('submit', confirmarVenta);
    const actualizar = () => { errorServidorCobro = ''; mostrarResumenCobro(); };
    document.getElementById('datos-cobro').addEventListener('input', actualizar);
    document.getElementById('datos-cobro').addEventListener('change', actualizar);
    document.getElementById('agregar-pago').addEventListener('click', () => {
        const usados = leerPagos().map(p => String(p.idMetodo));
        const opcion = [...document.querySelector('#plantilla-pago').content.querySelectorAll('option')].find(o => !usados.includes(o.value));
        if (opcion) agregarFilaPago(opcion.value, Math.max(0, mostrarResumenCobro().restante));
        mostrarResumenCobro();
    });
    document.querySelectorAll('[data-billete]').forEach(boton => boton.addEventListener('click', () => {
        const input = document.getElementById('efectivo-recibido');
        input.value = Number(input.value) + Number(boton.dataset.billete);
        mostrarResumenCobro();
    }));
    document.getElementById('efectivo-exacto').addEventListener('click', () => {
        document.getElementById('efectivo-recibido').value = mostrarResumenCobro().efectivo;
        mostrarResumenCobro();
    });
    document.getElementById('limpiar-efectivo').addEventListener('click', () => {
        document.getElementById('efectivo-recibido').value = '';
        mostrarResumenCobro();
    });
    try {
        const guardado = JSON.parse(localStorage.getItem(clavePendiente));
        if (guardado?.solicitud?.claveOperacion && Array.isArray(guardado.carrito)) {
            cobroPendiente = guardado;
            carrito = guardado.carrito;
            guardarYActualizar();
            document.getElementById('cliente_select').value = guardado.solicitud.rutCliente || '';
            guardado.solicitud.pagos.forEach(p => agregarFilaPago(p.idMetodo, p.monto));
            document.getElementById('efectivo-recibido').value = guardado.solicitud.efectivoRecibido ?? '';
            abrirConfirmacion();
            document.getElementById('error-cobro').textContent = 'Hay un cobro cuyo resultado no se confirmó. Reintenta para consultar o registrar esa misma operación.';
        }
    } catch {
        localStorage.removeItem(clavePendiente);
    }
}
