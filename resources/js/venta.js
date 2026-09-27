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

function actualizarBadges() {

    let badgeStock = document.getElementById('ticket-items');
    let badgeSeleccionado = document.getElementById('ticket-total');

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
// !! Esto está mal, el cobro no lo procesamos nosotros, pero despues lo arreglamos......... !!
window.procesarCobro = function (metodoPagoSeleccionado) {
    if (carrito.length === 0) {
        alert("El ticket está vacío. Agrega productos antes de cobrar.");
        return;
    }

    let tokenCsrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    // 1. Capturar el cliente seleccionado en la vista (si existe un select con id "cliente_select")
    let selectCliente = document.getElementById('cliente_select');
    let rutClienteSeleccionado = selectCliente ? selectCliente.value : null;

    // Convertir a null si el cajero dejó la opción "Ninguno" por defecto
    if (rutClienteSeleccionado === "Ninguno" || rutClienteSeleccionado === "") {
        rutClienteSeleccionado = null;
    }

    // 2. Calcular el total de la venta sumando cantidad * precio de cada ítem en el carrito
    let totalCalculado = carrito.reduce((acumulador, item) => {
        return acumulador + (item.cantidad * item.precio); // Asegúrate de usar el nombre de la llave de precio de tu carrito
    }, 0);

    // 3. Enviar la petición al backend inmediatamente
    fetch('/venta/registrar', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': tokenCsrf,
            'Accept': 'application/json' // Obliga a Laravel a devolver JSON incluso en errores
        },
        body: JSON.stringify({
            idTipo: 1, // ID fijo que corresponde a "Venta" en tu tabla de tipos de salida
            rutCliente: rutClienteSeleccionado,
            totalVenta: totalCalculado,
            metodoPago: metodoPagoSeleccionado,
            productos: carrito
        })
    })
        .then(response => response.json())
        .then(data => {
            // Validación directa de éxito
            if (data.success || response.ok) {
                alert("Venta registrada exitosamente.");

                // Limpieza inmediata del frontend
                carrito = [];
                localStorage.removeItem('carrito');

                if (typeof actualizarTicketVenta === 'function') {
                    actualizarTicketVenta();
                }

                // Opcional: limpiar montos y el selector de cliente en la interfaz
                if (selectCliente) selectCliente.value = "Ninguno";
                let inputMonto = document.getElementById('monto_recibido');
                if (inputMonto) inputMonto.value = "";

            } else {
                alert("Error al registrar: " + (data.message || data.error || "Datos inválidos."));
            }
        })
        .catch(error => {
            console.error('Error en la petición AJAX:', error);
            alert("Ocurrió un error de conexión al intentar guardar la venta en el sistema.");
        });
}