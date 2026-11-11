<!doctype html>
<html lang="es-CL">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#155e75">
    <link rel="manifest" href="/offline.webmanifest">
    <link rel="icon" href="/offline-icon.svg" type="image/svg+xml">
    <title>Congelandia · Trabajo offline</title>
    @vite('resources/js/offline/index.js')
</head>
<body>
<header><strong>Congelandia</strong><span>Trabajo offline provisional</span><a href="/pos">Volver al POS</a></header>
<main>
    <section class="notice" aria-label="Estado de conexión">
        <p id="connection" role="status">Comprobando almacenamiento local…</p>
        <p id="copy-info"></p>
        <p>El stock local es provisional y no reserva existencias. Otros equipos pueden haber registrado movimientos. Los cambios de permisos se comprueban al reconectar.</p>
        <div class="actions"><button id="prepare" type="button">Preparar / actualizar copia local</button><button id="sync" type="button">Sincronizar / reintentar</button><button id="install" type="button" hidden>Instalar aplicación</button><a href="/login">Iniciar sesión</a></div>
        <p id="message" role="status" aria-live="polite"></p>
    </section>
    <p id="first-use">El primer uso requiere conexión: inicia sesión, abre el POS y prepara la copia local en este navegador. No guardamos contraseñas ni credenciales.</p>
    <section id="online-pending" class="notice" hidden><h2>Cobro online sin confirmar</h2><p>Los datos están guardados en este equipo. <a href="/pos">Vuelve al POS</a> para reintentar ese mismo cobro con conexión.</p><details><summary>Ver los datos conservados</summary><pre id="online-pending-data"></pre></details></section>
    <div id="workspace" hidden>
        <nav aria-label="Operación"><button type="button" data-tab="sale" aria-pressed="true">Venta POS</button><button type="button" data-tab="movement" aria-pressed="false">Ingresos y mermas</button><button type="button" data-tab="queue" aria-pressed="false">Movimientos guardados</button></nav>
        <section id="sale" class="tab">
            <h1>Venta POS</h1>
            <div class="columns">
                <section><label for="search">Buscar producto por código o nombre</label><input id="search" type="search" autocomplete="off"><div id="products" class="products"></div></section>
                <section class="panel"><h2>Carrito</h2><ul id="cart"></ul><p class="total">Total <strong id="total">$0</strong></p>
                    <form id="sale-form">
                        <label for="client">Cliente existente (opcional)</label><select id="client"></select>
                        <h3>Pagos</h3><div id="payments"></div><button type="button" id="add-payment">Agregar método</button>
                        <label for="cash">Efectivo recibido del cliente</label><input id="cash" type="number" min="0" max="9999999999" step="1" inputmode="numeric">
                        <div class="actions" id="bills"><button type="button" data-bill="1000">+$1.000</button><button type="button" data-bill="2000">+$2.000</button><button type="button" data-bill="5000">+$5.000</button><button type="button" data-bill="10000">+$10.000</button><button type="button" data-bill="20000">+$20.000</button><button type="button" id="exact-cash">Efectivo exacto</button></div>
                        <p id="payment-summary" aria-live="polite"></p><button type="submit" class="primary">Revisar venta</button>
                    </form>
                </section>
            </div>
        </section>
        <section id="movement" class="tab" hidden>
            <h1>Ingresos y mermas</h1>
            <form id="movement-form" class="panel">
                <label for="kind">Movimiento</label><select id="kind"><option value="ingreso">Ingreso de mercadería</option><option value="merma">Salida por merma</option></select>
                <label for="product">Producto existente</label><select id="product" required></select>
                <p id="movement-stock"></p>
                <label for="quantity">Cantidad</label><input id="quantity" type="number" min="1" max="2147483647" step="1" required>
                <fieldset id="entry-fields"><legend>Datos del ingreso</legend>
                    <label for="supplier">Proveedor existente (opcional)</label><select id="supplier"></select>
                    <label for="cost">Precio de compra unitario</label><input id="cost" type="number" min="0" max="9999999999" step="0.01">
                    <label for="expiry">Fecha de vencimiento</label><input id="expiry" type="date">
                </fieldset>
                <fieldset id="loss-fields" hidden disabled><legend>Motivo existente</legend><label for="reason">Motivo de la merma</label><select id="reason"><option>Merma por Vencimiento</option><option>Merma por Daño</option></select></fieldset>
                <button type="submit" class="primary">Revisar movimiento</button>
            </form>
        </section>
        <section id="queue" class="tab" hidden><h1>Movimientos guardados</h1><p>Se envían en orden. Un conflicto detiene la cola y conserva todos sus datos. No se cambian automáticamente precios, cantidades ni pagos.</p><button type="button" id="export">Descargar copia de los movimientos</button><div id="operations"></div></section>
    </div>
    <dialog id="confirm-dialog"><form method="dialog"><h2>Confirmar operación</h2><pre id="confirm-details"></pre><p>Se guardará en este equipo como pendiente hasta recibir confirmación del servidor.</p><button value="cancel">Volver</button><button value="confirm" class="primary">Confirmar y guardar</button></form></dialog>
</main>
</body>
</html>
