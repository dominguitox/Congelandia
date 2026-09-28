// 1. Cargamos el carrito desde localStorage al iniciar, o iniciamos un arreglo vacío
let carrito = JSON.parse(localStorage.getItem('carrito')) || [];

// Al cargar la página, dibujamos inmediatamente los productos si existen en caché
document.addEventListener("DOMContentLoaded", () => {
    actualizarTicketVenta();
});

// Función auxiliar para guardar el estado actual en localStorage y actualizar la vista
function guardarYActualizar() {
    localStorage.setItem('carrito', JSON.stringify(carrito));
    actualizarTicketVenta();
}

window.agregarAlCarrito = function (idProducto, nombre, precioVenta, stockDisponible) {
    let indice = carrito.findIndex(item => item.id === idProducto);

    if (indice !== -1) {
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
            precio: precioVenta,
            stock: stockDisponible,
            cantidad: 1
        });
    }

    console.log(carrito);
    guardarYActualizar(); // Guarda en caché y actualiza la interfaz
}


function actualizarTicketVenta() {
    let contenedorTicket = document.getElementById('ticket-items');
    let spanTotal = document.getElementById('ticket-total');

    if (!contenedorTicket) return;

    contenedorTicket.innerHTML = '';
    let totalFinal = 0;

    if (carrito.length === 0) {
        contenedorTicket.innerHTML = `<p class="text-center text-muted mt-5">El carrito está vacío</p>`;
    } else {
        carrito.forEach((item, indice) => {
            let subtotal = item.precio * item.cantidad;
            totalFinal += subtotal;

            let fila = `
                <div class="d-flex justify-content-between align-items-center mb-2 p-2 border-bottom">
                    <div>
                        <h6 class="mb-0">${item.nombre}</h6>
                        <small class="text-muted">$${item.precio} c/u</small>
                    </div>
                    <div class="d-flex align-items-center">
                        <button class="btn btn-sm btn-outline-secondary px-2 me-1" onclick="restarAlCarrito(${indice})">-</button>
                        <span class="badge bg-secondary me-1">Cant. ${item.cantidad}</span>
                        <button class="btn btn-sm btn-outline-secondary px-2 me-2" onclick="sumarAlCarrito(${indice})">+</button>
                        <strong class="me-2">$${subtotal}</strong>
                        <button class="btn btn-sm btn-danger" onclick="eliminarDelCarrito(${indice})">&times;</button>
                    </div>
                </div>
            `;
            contenedorTicket.innerHTML += fila;
        });
    }

    if (spanTotal) {
        spanTotal.innerText = `$${totalFinal}`;
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
window.registrarVenta = function (e, metodoPagoSeleccionado) {
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

    // 5. Calcular el total
    let totalCalculado = carrito.reduce((acumulador, item) => {
        return acumulador + (item.cantidad * item.precio);
    }, 0);

    // 6. Enviar la petición al backend en Laravel
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
            alert("Error al registrar: " + (data.message || "Datos inválidos."));
        }
    })
    .catch(error => {
        console.error('Error en la petición AJAX:', error);
        alert("Ocurrió un error de conexión al intentar guardar la venta en el sistema.");
    });
}