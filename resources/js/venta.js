let carrito = [];

window.agregarAlCarrito = function (idProducto, nombre, precioVenta, stockDisponible) {
    let indice = carrito.findIndex(item => item.id === idProducto);

    if (indice !== -1) {
        if (carrito[indice].cantidad < stockDisponible) {
            carrito[indice].cantidad++;
        } else {
            alert("Stock insuficiente para " + nombre);
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
    actualizarTicketVenta(); // <--- Llamamos a la función que dibuja el ticket
}

function actualizarTicketVenta() {
    // Selecciona el contenedor donde van los items en tu ticket de venta
    let contenedorTicket = document.getElementById('ticket-items');
    let spanTotal = document.getElementById('ticket-total');

    // Si aún no tienes estos elementos creados en tu HTML, asegúrate de ponerles estos IDs
    if (!contenedorTicket) return;

    contenedorTicket.innerHTML = '';
    let totalFinal = 0;

    carrito.forEach((item, indice) => {
        let subtotal = item.precio * item.cantidad;
        totalFinal += subtotal;

        // Dibuja cada producto dentro del ticket visual
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

    if (spanTotal) {
        spanTotal.innerText = `$${totalFinal}`;
    }
}

window.eliminarDelCarrito = function (indice) {
    carrito.splice(indice, 1);
    actualizarTicketVenta();
}

window.sumarAlCarrito = function (indice) {
    // Valida que la cantidad actual no supere el stock disponible del producto
    if (carrito[indice].cantidad < carrito[indice].stock) {
        carrito[indice].cantidad++;
        actualizarTicketVenta();
    } else {
        alert("Stock insuficiente para " + carrito[indice].nombre);
    }
}

window.restarAlCarrito = function (indice) {
    // Si la cantidad es mayor a 1, simplemente resta uno
    if (carrito[indice].cantidad > 1) {
        carrito[indice].cantidad--;
        actualizarTicketVenta();
    } else {
        // Si llega a 1 y se vuelve a restar, elimina el producto del carrito
        eliminarDelCarrito(indice);
    }
}

window.procesarCobro = function (metodoPagoSeleccionado) {
    if (carrito.length === 0) {
        alert("El carrito está vacío.");
        return;
    }

    // Obtenemos el token CSRF obligatorio de Laravel desde el meta tag del HTML
    let tokenCsrf = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

    fetch('/venta/registrar', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': tokenCsrf
        },
        body: JSON.stringify({
            carrito: carrito,
            metodoPago: metodoPagoSeleccionado
        })
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                // Limpiamos el carrito local y la interfaz
                carrito = [];
                actualizarTicketVenta();
            } else {
                alert("Error: " + data.message);
            }
        })
        .catch(error => {
            console.error('Error en la petición:', error);
            alert("Ocurrió un error al procesar el pago.");
        });
}