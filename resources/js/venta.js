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
        alert("El carrito está vacío.");
        return;
    }

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
                // Limpiamos el carrito local, borramos la caché y actualizamos
                carrito = [];
                localStorage.removeItem('carrito');
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