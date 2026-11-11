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
<header><strong>Congelandia</strong><span>Trabajar sin internet</span><a href="/pos">Volver al POS</a></header>
<main>
    <section class="notice" aria-label="Estado de conexión">
        <p id="connection" role="status">Comprobando almacenamiento local…</p>
        <p id="copy-info"></p>
        <div class="transfer-actions">
            <article class="transfer-card transfer-card--download">
                <span class="transfer-direction">Servidor → Este equipo</span>
                <h2>Descargar datos</h2>
                <p>Trae los productos, precios y stock para usarlos cuando no tengas internet.</p>
                <button id="prepare" type="button">Descargar datos</button>
                <p id="download-help" class="transfer-help">Hazlo antes de desconectarte.</p>
            </article>
            <article class="transfer-card transfer-card--upload">
                <span class="transfer-direction">Este equipo → Servidor</span>
                <h2>Enviar pendientes</h2>
                <p>Envía las ventas, ingresos y salidas guardados en este equipo para registrarlos en el sistema.</p>
                <button id="sync" type="button" class="primary" disabled>Enviar pendientes</button>
                <p id="upload-help" class="transfer-help">Todavía no hay movimientos por enviar.</p>
            </article>
        </div>
        <div class="actions secondary-actions"><button id="install" type="button" hidden>Instalar aplicación</button><button id="update-app" type="button" hidden>Actualizar pantalla</button><a id="login-link" href="/login">Iniciar sesión</a></div>
        <p id="message" role="status" aria-live="polite"></p>
        <details class="offline-help"><summary>¿Cómo trabajo sin internet?</summary><ol><li>Con internet, descarga los datos una vez en este equipo.</li><li>Registra ventas, ingresos o mermas. Quedan guardados aquí aunque recargues.</li><li>Al volver internet, los movimientos se envían automáticamente. También puedes pulsar Enviar pendientes.</li></ol><p>El stock descargado es provisional: otros equipos pueden registrar movimientos. El servidor comprueba los precios, el stock y los permisos al recibirlos.</p></details>
    </section>
    <p id="first-use">Para empezar, inicia sesión con internet y pulsa <strong>Descargar datos</strong>. Después podrás trabajar sin conexión en este mismo navegador.</p>
    <section id="online-pending" class="notice" hidden><h2>Cobro online sin confirmar</h2><p>Los datos están guardados en este equipo. <a href="/pos">Vuelve al POS</a> para reintentar ese mismo cobro con conexión.</p><details><summary>Ver los datos conservados</summary><pre id="online-pending-data"></pre></details></section>
    <div id="workspace" hidden>
        <nav aria-label="Operación"><button type="button" data-tab="sale" aria-pressed="true">Vender</button><button type="button" data-tab="movement" aria-pressed="false">Ingresos y salidas</button><button id="queue-tab" type="button" data-tab="queue" aria-pressed="false">Movimientos guardados</button></nav>
        <section id="sale" class="tab">
            <h1>Venta POS</h1>
            <div class="columns">
                <section><label for="search">Buscar producto por código o nombre</label><input id="search" type="search" autocomplete="off"><div id="products" class="products"></div></section>
                <section class="panel"><h2>Carrito</h2><ul id="cart"></ul><p class="total">Total <strong id="total">$0</strong></p>
                    <form id="sale-form">
                        <label for="client">Cliente existente (opcional)</label><select id="client"></select>
                        <h3>Forma de pago</h3><div id="payments"></div><button type="button" id="add-payment">Dividir entre dos medios de pago</button>
                        <section id="cash-fields" hidden><label for="cash">Efectivo recibido</label><input id="cash" type="number" min="0" max="9999999999" step="1" inputmode="numeric" placeholder="Si paga justo, déjalo vacío" aria-describedby="cash-help"><p id="cash-help" class="transfer-help">Escribe el total que te entregan. Si paga justo, puedes dejarlo vacío.</p></section>
                        <p id="payment-summary" aria-live="polite"></p><button type="submit" class="primary">Revisar venta</button>
                    </form>
                </section>
            </div>
        </section>
        <section id="movement" class="tab" hidden>
            <h1>Ingresos y salidas de inventario</h1>
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
        <section id="queue" class="tab" hidden><h1>Movimientos guardados</h1><p>Los movimientos por enviar siguen guardados en este equipo. Si alguno necesita revisión, los siguientes esperan su turno.</p><button type="button" id="export">Exportar movimientos para revisión</button><div id="operations"></div></section>
    </div>
    <dialog id="confirm-dialog"><form method="dialog"><h2>Revisar antes de guardar</h2><pre id="confirm-details"></pre><p>El movimiento quedará guardado en este equipo. Se enviará al servidor cuando haya conexión.</p><button value="cancel">Volver</button><button value="confirm" class="primary">Guardar movimiento</button></form></dialog>
</main>
</body>
</html>
