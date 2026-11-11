import '../../css/offline.css';
import { calcularCobro } from '../cobro.js';
import { localStore, onLocalChange, pending } from './store.js';
import { prepareOffline, serverSession, syncQueue } from './sync.js';
import { onShellUpdate, registerShell } from './register.js';
import { operationTitle, statusLabel, transferState } from './ui.js';

const $ = id => document.getElementById(id);
const money = value => '$' + new Intl.NumberFormat('es-CL').format(value || 0);
const store = localStore();
const option = (value, text) => new Option(text, value);
let user, account, draft, installPrompt, confirmation;
let activity = '', saving = false, serverUnavailable = false;
let writes = Promise.resolve();

function message(text, error = false) {
    $('message').textContent = text;
    $('message').dataset.kind = error ? 'error' : 'success';
}

function save() {
    const value = structuredClone(draft);
    writes = writes.catch(() => {}).then(() => store.saveDraft(user, value));
    writes.catch(error => message('No se pudo guardar el borrador: ' + error.message, true));
    return writes;
}

const total = () => (draft?.carrito || []).reduce((sum, item) => sum + item.cantidad * item.precio, 0);

function payments() {
    return (draft.pagos || []).map(payment => ({ ...payment,
        esEfectivo: account.snapshot.metodos.find(method => Number(method.idMetodo) === Number(payment.idMetodo))?.nombre.trim().toLowerCase() === 'efectivo',
    }));
}

function receivedCash() {
    const exact = payments().filter(payment => payment.esEfectivo).reduce((sum, payment) => sum + payment.monto, 0);
    return String(draft.recibido ?? '').trim() === '' ? exact : Number(draft.recibido);
}

function paymentSummary() {
    const result = calcularCobro(total(), payments(), receivedCash());
    $('cash-fields').hidden = result.efectivo <= 0;
    $('payment-summary').textContent = result.error || (result.efectivo > 0 ? `Vuelto a entregar: ${money(result.vuelto)}` : 'Pago completo.');
    return result;
}

function renderPayments() {
    draft.pagos ||= [];
    if (!draft.pagos.length && account.snapshot.metodos.length) draft.pagos.push({ idMetodo: Number(account.snapshot.metodos[0].idMetodo), monto: total() });
    const single = draft.pagos.length === 1;
    if (single) draft.pagos[0].monto = total();
    $('payments').replaceChildren();
    draft.pagos.forEach((payment, index) => {
        const row = document.createElement('div'); row.className = 'payment-row' + (single ? ' payment-row--single' : '');
        const methodLabel = document.createElement('label'); methodLabel.textContent = single ? 'Método de pago' : 'Método';
        const method = document.createElement('select'); method.required = true;
        account.snapshot.metodos.forEach(item => method.add(option(item.idMetodo, item.nombre)));
        method.value = String(payment.idMetodo);
        method.addEventListener('change', () => { payment.idMetodo = Number(method.value); save(); paymentSummary(); });
        methodLabel.append(method);
        const amountLabel = document.createElement('label'); amountLabel.textContent = 'Monto de este pago'; amountLabel.hidden = single;
        const amount = document.createElement('input'); amount.type = 'number'; amount.min = '0'; amount.max = '9999999999'; amount.step = '1'; amount.required = !single; amount.value = payment.monto;
        amount.addEventListener('input', () => { payment.monto = Number(amount.value); save(); paymentSummary(); });
        amountLabel.append(amount);
        const remove = document.createElement('button'); remove.type = 'button'; remove.textContent = 'Quitar'; remove.hidden = single;
        remove.addEventListener('click', () => { draft.pagos.splice(index, 1); renderPayments(); save(); });
        row.append(methodLabel, amountLabel, remove); $('payments').append(row);
    });
    $('cash').value = draft.recibido ?? '';
    $('add-payment').textContent = single ? 'Dividir entre dos medios de pago' : 'Agregar otro medio de pago';
    $('add-payment').disabled = draft.pagos.length >= account.snapshot.metodos.length;
    paymentSummary();
}

function renderCart() {
    $('cart').replaceChildren();
    for (const item of draft.carrito) {
        const row = document.createElement('li');
        const label = document.createElement('span'); label.textContent = `${item.cantidad} × ${item.nombre} · ${money(item.cantidad * item.precio)}`;
        row.append(label);
        for (const [text, delta] of [['−', -1], ['+', 1]]) {
            const button = document.createElement('button'); button.type = 'button'; button.textContent = text;
            button.setAttribute('aria-label', `${delta > 0 ? 'Sumar' : 'Quitar'} una unidad de ${item.nombre}`);
            button.addEventListener('click', () => {
                const product = account.snapshot.productos.find(product => product.codigo === item.id);
                if (delta > 0 && item.cantidad >= (product?.stock || 0)) return message('La cantidad supera el stock provisional.', true);
                item.cantidad += delta;
                if (item.cantidad < 1) draft.carrito = draft.carrito.filter(other => other !== item);
                renderCart(); save();
            }); row.append(button);
        }
        $('cart').append(row);
    }
    $('total').textContent = money(total()); renderPayments();
}

function renderProducts() {
    const search = $('search').value.trim().toLocaleLowerCase('es');
    $('products').replaceChildren();
    account.snapshot.productos.filter(product => `${product.codigo} ${product.nombre}`.toLocaleLowerCase('es').includes(search)).forEach(product => {
        const button = document.createElement('button'); button.type = 'button';
        const title = document.createElement('strong'); title.textContent = product.nombre;
        const price = document.createElement('span'); price.textContent = product.precio === null ? 'Sin precio vigente' : money(product.precio) + (product.descuento ? ` (−${product.descuento}%)` : '');
        const stock = document.createElement('small'); stock.textContent = `Stock en este equipo: ${product.stock}`;
        button.append(title, price, stock); button.disabled = product.stock < 1 || product.precio === null;
        button.addEventListener('click', () => {
            const item = draft.carrito.find(item => item.id === product.codigo);
            if ((item?.cantidad || 0) >= product.stock) return message('La cantidad supera el stock provisional.', true);
            if (item) item.cantidad++;
            else draft.carrito.push({ id: product.codigo, nombre: product.nombre, precio: product.precio, cantidad: 1 });
            renderCart(); save();
        }); $('products').append(button);
    });
}

function renderQueue() {
    $('operations').replaceChildren();
    for (const row of account.operations) {
        const article = document.createElement('article'); article.className = 'operation'; article.dataset.state = row.status;
        const heading = document.createElement('div'); heading.className = 'operation-heading';
        const title = document.createElement('h3'); title.textContent = operationTitle(row.operation);
        const badge = document.createElement('span'); badge.className = 'operation-status'; badge.textContent = statusLabel(row.status);
        heading.append(title, badge);
        const date = document.createElement('p'); date.textContent = new Date(row.operation.fechaLocal).toLocaleString('es-CL');
        const items = row.operation.tipo === 'venta' ? row.operation.datos.productos : [{ id: row.operation.datos.codigoProducto, cantidad: row.operation.datos.cantidad }];
        const description = document.createElement('p'); description.textContent = items.map(item => `${item.cantidad} × ${account.snapshot.productos.find(product => product.codigo === item.id)?.nombre || item.id}`).join(' · ');
        const status = document.createElement('p'); status.textContent = row.message;
        const details = document.createElement('details');
        const summary = document.createElement('summary'); summary.textContent = 'Datos originales para revisión';
        const original = document.createElement('pre'); original.textContent = JSON.stringify({ operacion: row.operation, respuesta: row.response }, null, 2);
        details.append(summary, original); article.append(heading, date, description, status, details); $('operations').append(article);
    }
}

function renderStatus() {
    const state = transferState(account, navigator.onLine, activity);
    $('connection').textContent = activity === 'upload' ? 'Enviando movimientos al servidor…'
        : navigator.onLine && !serverUnavailable ? 'Con conexión · puedes enviar los movimientos' : 'Sin conexión · tus movimientos se guardan en este equipo';
    $('copy-info').textContent = account?.snapshot ? `${account.snapshot.usuario.nombre} · Datos descargados el ${new Date(account.snapshot.actualizado).toLocaleString('es-CL')}` : 'Todavía no descargaste los datos en este equipo.';
    $('prepare').disabled = state.downloadDisabled; $('prepare').textContent = state.downloadLabel;
    $('sync').disabled = state.uploadDisabled; $('sync').textContent = state.uploadLabel;
    $('download-help').textContent = state.downloadHelp; $('upload-help').textContent = state.uploadHelp;
    $('queue-tab').textContent = `Movimientos guardados${state.count ? ` (${state.count} por enviar)` : ''}`;
}

function selectTab(tab) {
    document.querySelectorAll('.tab').forEach(section => { section.hidden = section.id !== tab; });
    document.querySelectorAll('[data-tab]').forEach(button => button.setAttribute('aria-pressed', String(button.dataset.tab === tab)));
}

async function refresh(redraw = false) {
    await writes;
    const current = await store.read('active');
    const changedUser = Number(current) !== Number(user);
    user = current; account = user ? await store.account(user) : null;
    const ready = Boolean(account?.snapshot);
    $('online-pending').hidden = !account?.draft?.pending;
    $('online-pending-data').textContent = account?.draft?.pending ? JSON.stringify(account.draft.pending, null, 2) : '';
    $('workspace').hidden = !ready; $('first-use').hidden = ready; renderStatus();
    if (!ready) return;
    if (redraw || changedUser || !draft) {
        draft = structuredClone(account.draft || { carrito: [] }); draft.carrito ||= [];
        $('client').replaceChildren(option('', 'Sin cliente'));
        account.snapshot.clientes.forEach(client => $('client').add(option(client.rutCliente, client.nombre)));
        $('client').value = draft.cliente || '';
        $('supplier').replaceChildren(option('', 'Sin proveedor'));
        account.snapshot.proveedores.forEach(supplier => $('supplier').add(option(supplier.idProveedor, supplier.nombre)));
        $('product').replaceChildren(...account.snapshot.productos.map(product => option(product.codigo, product.nombre + ' · ' + product.codigo)));
        $('movement-form').reset();
        for (const [id, value] of Object.entries(draft.movimiento || {})) if ($(id)) $(id).value = value;
        selectTab(draft.tab || 'sale'); renderCart();
    }
    movementFields(); renderProducts(); renderQueue();
}

function movementFields() {
    const entry = $('kind').value === 'ingreso';
    $('entry-fields').hidden = !entry; $('entry-fields').disabled = !entry;
    $('loss-fields').hidden = entry; $('loss-fields').disabled = entry;
    $('cost').required = entry; $('expiry').required = entry;
    const product = account?.snapshot?.productos.find(product => product.codigo === $('product').value);
    $('movement-stock').textContent = product ? 'Stock en este equipo: ' + product.stock : '';
}

async function synchronize() {
    if (activity || !user || !navigator.onLine || $('confirm-dialog').open) return;
    await writes;
    if (!pending(await store.account(user)).length) return;
    activity = 'upload'; renderStatus();
    try {
        const result = await syncQueue(store, user, fetch, () => refresh());
        serverUnavailable = false;
        message(result.sent === 1 ? 'Movimiento enviado y registrado en el servidor.' : `${result.sent} movimientos enviados y registrados en el servidor.`);
    } catch (error) { serverUnavailable = error.code === 'network'; message(error.message, true); }
    finally { activity = ''; await refresh(); }
}

$('prepare').addEventListener('click', async () => {
    if (activity) return;
    activity = 'download'; renderStatus();
    try {
        await writes;
        const session = await serverSession(); user = session.usuario; await store.bind(user);
        if (pending(await store.account(user)).length) throw new Error('Primero envía los movimientos pendientes. Después podrás descargar datos nuevos.');
        await registerShell(); await prepareOffline(store, user);
        serverUnavailable = false;
        message('Datos descargados. Ya puedes registrar ventas, ingresos y salidas sin internet.');
        await refresh(true);
    } catch (error) { serverUnavailable = error.code === 'network'; message(error.message, true); }
    finally { activity = ''; await refresh(); }
});
$('sync').addEventListener('click', synchronize);
$('search').addEventListener('input', renderProducts);
$('client').addEventListener('change', () => { draft.cliente = $('client').value; save(); });
$('add-payment').addEventListener('click', () => {
    const method = account.snapshot.metodos.find(method => !draft.pagos.some(payment => Number(payment.idMetodo) === Number(method.idMetodo)));
    if (!method) return;
    draft.pagos.push({ idMetodo: Number(method.idMetodo), monto: Math.max(0, total() - draft.pagos.reduce((sum, payment) => sum + payment.monto, 0)) });
    renderPayments(); save();
});
$('cash').addEventListener('input', () => { draft.recibido = $('cash').value; save(); paymentSummary(); });
$('sale-form').addEventListener('submit', event => {
    event.preventDefault();
    const summary = paymentSummary();
    if (!draft.carrito.length || !summary.valido) return message(summary.error || 'Agrega productos antes de cobrar.', true);
    if (draft.pending) return message('Hay un cobro online sin confirmar. Reinténtalo desde el POS antes de crear otra venta.', true);
    confirmation = { tipo: 'venta', datos: {
        idTipo: account.snapshot.tipoVenta, rutCliente: draft.cliente || null,
        productos: draft.carrito.map(({ id, cantidad, precio }) => ({ id, cantidad, precio })),
        pagos: draft.pagos.map(({ idMetodo, monto }) => ({ idMetodo, monto })),
        efectivoRecibido: summary.efectivo > 0 ? receivedCash() : null,
    } };
    $('confirm-details').textContent = draft.carrito.map(item => `${item.cantidad} × ${item.nombre}: ${money(item.cantidad * item.precio)}`).join('\n') +
        `\nTotal: ${money(total())}\n` + payments().map(payment => `${account.snapshot.metodos.find(method => Number(method.idMetodo) === payment.idMetodo)?.nombre}: ${money(payment.monto)}`).join('\n') + `\nVuelto: ${money(summary.vuelto)}`;
    $('confirm-dialog').showModal();
});
$('movement-form').addEventListener('input', () => {
    draft.movimiento = Object.fromEntries(['kind', 'product', 'quantity', 'supplier', 'cost', 'expiry', 'reason'].map(id => [id, $(id).value]));
    movementFields(); save();
});
$('movement-form').addEventListener('change', movementFields);
$('movement-form').addEventListener('submit', event => {
    event.preventDefault();
    const entry = $('kind').value === 'ingreso';
    const quantity = Number($('quantity').value);
    const product = account.snapshot.productos.find(product => product.codigo === $('product').value);
    if (!product || !Number.isSafeInteger(quantity) || quantity < 1 || (!entry && quantity > product.stock)) return message('Revisa la cantidad y el stock disponible en este equipo.', true);
    const today = new Date();
    const localDate = `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}-${String(today.getDate()).padStart(2, '0')}`;
    if (entry && $('expiry').value < localDate) return message('El vencimiento debe ser hoy o posterior.', true);
    confirmation = { tipo: $('kind').value, datos: {
        codigoProducto: product.codigo, cantidad: quantity,
        ...(entry ? { idProveedor: Number($('supplier').value) || null, precioCompra: Number($('cost').value), fechaVencimiento: $('expiry').value } : { motivo: $('reason').value }),
    } };
    $('confirm-details').textContent = `${entry ? 'Ingreso de mercadería' : $('reason').value}\n${product.nombre}: ${quantity} ${quantity === 1 ? 'unidad' : 'unidades'}` + (entry ? `\nPrecio de compra: ${money($('cost').value)}\nVencimiento: ${$('expiry').value}\nProveedor: ${$('supplier').selectedOptions[0].textContent}` : '');
    $('confirm-dialog').showModal();
});
$('confirm-dialog').addEventListener('close', async () => {
    if ($('confirm-dialog').returnValue !== 'confirm' || !confirmation || saving) return;
    saving = true;
    try {
        await writes;
        const operation = { ...confirmation, id: crypto.randomUUID(), usuario: user, fechaLocal: new Date().toISOString() };
        await store.enqueue(user, operation);
        await store.mutate(user, account => { account.draft.tab = operation.tipo === 'venta' ? 'sale' : 'movement'; });
        await refresh(true);
        message(`Guardado en este equipo: ${operationTitle(operation)}.${navigator.onLine ? ' Intentando enviarlo al servidor…' : ' Se enviará cuando vuelva internet.'}`);
    } catch (error) { message(error.message, true); }
    finally { confirmation = null; saving = false; }
    if (navigator.onLine) await synchronize();
});
document.querySelectorAll('[data-tab]').forEach(button => button.addEventListener('click', () => {
    draft.tab = button.dataset.tab; selectTab(draft.tab); save();
}));
$('export').addEventListener('click', async () => {
    await writes;
    const saved = await store.account(user);
    const url = URL.createObjectURL(new Blob([JSON.stringify({ usuario: user, operaciones: saved.operations, borrador: saved.draft }, null, 2)], { type: 'application/json' }));
    const link = document.createElement('a'); link.href = url; link.download = 'congelandia-movimientos-' + user + '.json'; link.click();
    setTimeout(() => URL.revokeObjectURL(url), 1000);
});
window.addEventListener('beforeinstallprompt', event => { event.preventDefault(); installPrompt = event; $('install').hidden = false; });
$('install').addEventListener('click', async () => { await installPrompt?.prompt(); $('install').hidden = true; });
onShellUpdate(() => { $('update-app').hidden = false; });
$('update-app').addEventListener('click', async () => { await writes; location.reload(); });
window.addEventListener('online', () => { serverUnavailable = false; refresh().then(synchronize).catch(error => message(error.message, true)); });
window.addEventListener('offline', () => refresh().catch(error => message(error.message, true)));
onLocalChange(() => { if (!saving && !$('confirm-dialog').open) refresh().catch(error => message(error.message, true)); });

(async () => {
    // Mostrar lo ya guardado antes de esperar una red que puede estar caída.
    await refresh(true);
    if (navigator.onLine) {
        try {
            const session = await serverSession(fetch, 4000);
            if (Number(session.usuario) !== Number(user)) { await store.bind(session.usuario); await refresh(true); }
        } catch (error) { serverUnavailable = error.code === 'network'; if (!account?.snapshot || error.code !== 'network') message(error.message, true); }
        renderStatus();
        if (user && account?.snapshot) await synchronize();
    }
})().catch(error => message(error.message, true));
