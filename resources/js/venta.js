// 1. Cargamos el carrito desde localStorage al iniciar, o iniciamos un arreglo vacío
let carrito = JSON.parse(localStorage.getItem('carrito')) || [];

// Al cargar la página, dibujamos inmediatamente los productos si existen en caché
document.addEventListener("DOMContentLoaded", () => {
    sincronizarPreciosDelCatalogo();
    actualizarTicketVenta();

    document.querySelectorAll('.producto-card').forEach((card) => {
        card.addEventListener('click', () => agregarAlCarrito(card));
        card.addEventListener('keydown', (event) => {
            if (event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                agregarAlCarrito(card);
            }
        });
    });
});

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
    carrito.splice(indice, 1);
    guardarYActualizar();
}

window.sumarAlCarrito = function (indice) {
    if (carrito[indice].cantidad < carrito[indice].stock) {
        carrito[indice].cantidad++;
        guardarYActualizar();
    } else {
        alert("Stock insuficiente para " + carrito[indice].nombre);
    }
}

window.restarAlCarrito = function (indice) {
    if (carrito[indice].cantidad > 1) {
        carrito[indice].cantidad--;
        guardarYActualizar();
    } else {
        eliminarDelCarrito(indice);
    }
}
window.registrarVenta = function (e) {
    // 1. Prevenir la recarga de la página si el botón está en un formulario
    if (e) e.preventDefault();

    // 2. Validar que el carrito exista globalmente y tenga productos
    if (typeof carrito === 'undefined' || carrito.length === 0) {
        alert("El ticket está vacío. Agrega productos antes de cobrar.");
        return;
    }

    // 3. Extracción segura del token CSRF para Laravel
    let metaCsrf = document.querySelector('meta[name="csrf-token"]');
    if (!metaCsrf) {
        console.error("Error: Falta la etiqueta <meta name='csrf-token'> en el HTML (Blade).");
        alert("Error de configuración del sistema (Token CSRF faltante).");
        return;
    }
    let tokenCsrf = metaCsrf.getAttribute('content');

    // 4. Capturar el cliente (opcional)
    let selectCliente = document.getElementById('cliente_select');
    let rutClienteSeleccionado = selectCliente ? selectCliente.value : null;

    if (rutClienteSeleccionado === "Ninguno" || rutClienteSeleccionado === "") {
        rutClienteSeleccionado = null;
    }

    // 5. Capturar el método de pago seleccionado
    let selectMetodo = document.getElementById('metodo_pago_select');
    let metodoPagoSeleccionado = selectMetodo ? selectMetodo.value : null;

    if (!metodoPagoSeleccionado) {
        alert("Selecciona un método de pago.");
        return;
    }

    // 6. Calcular el total
    let totalCalculado = carrito.reduce((acumulador, item) => {
        return acumulador + (item.cantidad * item.precio);
    }, 0);

    // 7. Enviar la petición al backend en Laravel
    fetch('/venta/registrar', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': tokenCsrf,
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            idTipo: 1, // ID fijo configurado para identificar "Venta" vs "Merma"
            rutCliente: rutClienteSeleccionado,
            totalVenta: totalCalculado,
            metodoPago: metodoPagoSeleccionado,
            productos: carrito
        })
    })
        .then(response => response.json())
        .then(data => {
            console.log(data);
            if (data.success || data.ok) {
                alert("Venta registrada exitosamente.");

                carrito.forEach(item => {
                    let badgeStock = document.getElementById('badge-stock-' + item.id);
                    if (badgeStock) {
                        let stockInicial = parseInt(badgeStock.getAttribute('data-stock-inicial'));
                        // Restamos lo vendido al stock base del HTML
                        let nuevoStockBase = stockInicial - item.cantidad;
                        badgeStock.setAttribute('data-stock-inicial', nuevoStockBase);
                    }
                });
                
                // Limpiar carrito
                carrito = [];
                localStorage.removeItem('carrito');

                if (typeof actualizarTicketVenta === 'function') {
                    actualizarTicketVenta();
                }

                // Limpiar interfaz
                if (selectCliente) selectCliente.value = "Ninguno";
                let inputMonto = document.getElementById('monto_recibido');
                if (inputMonto) inputMonto.value = "";

            } else {
                alert("Error al registrar: " + (data.error || data.message || "Datos inválidos."));
            }
        })
        .catch(error => {
            console.error('Error en la petición AJAX:', error);
            alert("Ocurrió un error de conexión al intentar guardar la venta en el sistema.");
        });
}
