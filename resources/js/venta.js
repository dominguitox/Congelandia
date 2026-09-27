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
                    <span class="badge bg-secondary me-2">Qty: ${item.cantidad}</span>
                    <strong class="me-3">$${subtotal}</strong>
                <div style= "gap: 4px">
                    <button class="btn btn-sm btn-warning" onclick="restarAlCarrito(${indice})">-</button>
                    <button class="btn btn-sm btn-danger" onclick="eliminarDelCarrito(${indice})">&times;</button>
                    <button class="btn btn-sm btn-success" onclick="sumarAlCarrito(${indice})">+</button>
                </div>
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
window.actualizrEtiquetaStock = function () {

}