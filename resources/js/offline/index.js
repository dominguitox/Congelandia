import '../../css/offline.css';
import { calcularCobro } from '../cobro.js';
import { localStore, onLocalChange, pending } from './store.js';
import { prepareOffline, serverSession, syncQueue } from './sync.js';
import { registerShell } from './register.js';

const $ = id => document.getElementById(id);
const money = value => '$' + new Intl.NumberFormat('es-CL').format(value || 0);
const store = localStore();
let user, account, draft, busy = false, installPrompt, confirmation;
let serverUnavailable = false;
let writes = Promise.resolve();
const message = text => { $('message').textContent = text; };
const option = (value, text) => new Option(text, value);
const total = () => (draft?.carrito || []).reduce((sum, item) => sum + item.cantidad * item.precio, 0);

function save() {
    const value = structuredClone(draft);
    writes = writes.catch(() => {}).then(() => store.saveDraft(user, value));
    writes.catch(error => message('No se pudo guardar el borrador: ' + error.message));
    return writes;
}

function payments() {
    return (draft.pagos || []).map(payment => ({ ...payment,
        esEfectivo: account.snapshot.metodos.find(method => Number(method.idMetodo) === Number(payment.idMetodo))?.nombre.trim().toLowerCase() === 'efectivo',
    }));
}

function paymentSummary() {
    const result = calcularCobro(total(), payments(), Number(draft.recibido || 0));
    $('payment-summary').textContent = `Aplicado: ${money(result.asignado)} · Por asignar: ${money(result.restante)} · Vuelto: ${money(result.vuelto)}. ${result.error}`;
    return result;
}

function renderPayments() {
    $('payments').replaceChildren();
    (draft.pagos || []).forEach((payment, index) => {
        const row = document.createElement('div'); row.className = 'payment-row';
        const methodLabel = document.createElement('label'); methodLabel.textContent = 'Método';
        const method = document.createElement('select'); method.required = true;
        account.snapshot.metodos.forEach(item => method.add(option(item.idMetodo, item.nombre)));
        method.value = String(payment.idMetodo);
        method.addEventListener('change', () => { payment.idMetodo = Number(method.value); save(); paymentSummary(); });
        methodLabel.append(method);
        const amountLabel = document.createElement('label'); amountLabel.textContent = 'Importe aplicado';
        const amount = document.createElement('input'); amount.type = 'number'; amount.min = '0'; amount.max = '9999999999'; amount.step = '1'; amount.required = true; amount.value = payment.monto;
        amount.addEventListener('input', () => { payment.monto = Number(amount.value); save(); paymentSummary(); });
        amountLabel.append(amount);
        const remove = document.createElement('button'); remove.type = 'button'; remove.textContent = 'Quitar';
        remove.addEventListener('click', () => { draft.pagos.splice(index, 1); save(); renderPayments(); });
        row.append(methodLabel, amountLabel, remove); $('payments').append(row);
    });
    $('cash').value = draft.recibido ?? '';
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
                if (delta > 0 && item.cantidad >= (product?.stock || 0)) return message('La cantidad supera el stock provisional.');
                item.cantidad += delta;
                if (item.cantidad < 1) draft.carrito = draft.carrito.filter(other => other !== item);
                save(); renderCart();
            }); row.append(button);
        }
        $('cart').append(row);
    }
    $('total').textContent = money(total()); paymentSummary();
}

function renderProducts() {
    const search = $('search').value.trim().toLocaleLowerCase('es');
    $('products').replaceChildren();
    account.snapshot.productos.filter(product => `${product.codigo} ${product.nombre}`.toLocaleLowerCase('es').includes(search)).forEach(product => {
        const button = document.createElement('button'); button.type = 'button';
        const title = document.createElement('strong'); title.textContent = product.nombre;
        const price = document.createElement('span'); price.textContent = product.precio === null ? 'Sin precio vigente' : money(product.precio) + (product.descuento ? ` (−${product.descuento}%)` : '');
        const stock = document.createElement('small'); stock.textContent = `${product.codigo} · Stock provisional: ${product.stock}`;
        button.append(title, price, stock); button.disabled = product.stock < 1 || product.precio === null;
        button.addEventListener('click', () => {
            let item = draft.carrito.find(item => item.id === product.codigo);
            if ((item?.cantidad || 0) >= product.stock) return message('La cantidad supera el stock provisional.');
            if (item) item.cantidad++;
            else draft.carrito.push({ id: product.codigo, nombre: product.nombre, precio: product.precio, cantidad: 1 });
            save(); renderCart();
        }); $('products').append(button);
    });
}

function renderQueue() {
    $('operations').replaceChildren();
    if (account.draft?.pending) {
        const details = document.createElement('details'); details.className = 'operation';
        const summary = document.createElement('summary'); summary.textContent = 'Cobro online pendiente de confirmación · reintentar desde el POS';
        const original = document.createElement('pre'); original.textContent = JSON.stringify(account.draft.pending, null, 2);
        details.append(summary, original); $('operations').append(details);
    }
    for (const row of account.operations) {
        const details = document.createElement('details'); details.className = 'operation'; details.dataset.state = row.status;
        const summary = document.createElement('summary'); summary.textContent = `${row.operation.tipo} · ${new Date(row.operation.fechaLocal).toLocaleString('es-CL')} · ${row.status}`;
        const status = document.createElement('p'); status.textContent = row.message;
        const original = document.createElement('pre'); original.textContent = JSON.stringify({ operacion: row.operation, respuesta: row.response }, null, 2);
        details.append(summary, status, original); $('operations').append(details);
    }
}

async function refresh() {
    user = await store.read('active');
    account = user ? await store.account(user) : null;
    const ready = Boolean(account?.snapshot);
    $('online-pending').hidden = !account?.draft?.pending;
    $('online-pending-data').textContent = account?.draft?.pending ? JSON.stringify(account.draft.pending, null, 2) : '';
    $('workspace').hidden = !ready; $('first-use').hidden = ready;
    $('connection').textContent = `${navigator.onLine && !serverUnavailable ? 'Red disponible' : 'Sin conexión con el servidor'} · ${pending(account).length} movimientos pendientes`;
    $('copy-info').textContent = ready ? `${account.snapshot.usuario.nombre} · Copia del ${new Date(account.snapshot.actualizado).toLocaleString('es-CL')}.` : 'No hay una copia preparada para el usuario activo.';
    $('sync').disabled = busy || !ready; $('prepare').disabled = busy || !navigator.onLine;
    if (!ready) return;
    draft = structuredClone(account.draft || { carrito: [] }); draft.carrito ||= [];
    $('client').replaceChildren(option('', 'Sin cliente'));
    account.snapshot.clientes.forEach(client => $('client').add(option(client.rutCliente, client.nombre)));
    $('client').value = draft.cliente || '';
    $('supplier').replaceChildren(option('', 'Sin proveedor'));
    account.snapshot.proveedores.forEach(supplier => $('supplier').add(option(supplier.idProveedor, supplier.nombre)));
    $('product').replaceChildren(...account.snapshot.productos.map(product => option(product.codigo, product.nombre + ' · ' + product.codigo)));
    for (const [id, value] of Object.entries(draft.movimiento || {})) if ($(id)) $(id).value = value;
    movementFields(); renderPayments(); renderCart(); renderProducts(); renderQueue();
}

function movementFields() {
    const entry = $('kind').value === 'ingreso';
    $('entry-fields').hidden = !entry; $('entry-fields').disabled = !entry;
    $('loss-fields').hidden = entry; $('loss-fields').disabled = entry;
    $('cost').required = entry; $('expiry').required = entry;
    const product = account?.snapshot?.productos.find(product => product.codigo === $('product').value);
    $('movement-stock').textContent = product ? 'Stock provisional: ' + product.stock : '';
}

async function synchronize() {
    if (busy || !user || $('confirm-dialog').open) return;
    busy = true; $('sync').disabled = true;
    try { await writes; await syncQueue(store, user); serverUnavailable = false; message('Cola sincronizada. Puedes actualizar la copia local.'); }
    catch (error) { serverUnavailable = error.code === 'network'; message(error.message); }
    finally { busy = false; await refresh(); }
}

$('prepare').addEventListener('click', async () => {
    if (busy) return;
    busy = true;
    try {
        await writes;
        const session = await serverSession();
        user = session.usuario; await store.bind(user);
        await prepareOffline(store, user);
        await registerShell();
        serverUnavailable = false;
        message('Copia local y recursos preparados. Ya puedes trabajar durante un corte de conexión.');
    } catch (error) { message(error.message); }
    finally { busy = false; await refresh(); }
});
$('sync').addEventListener('click', synchronize);
$('search').addEventListener('input', renderProducts);
$('client').addEventListener('change', () => { draft.cliente = $('client').value; save(); });
$('add-payment').addEventListener('click', () => {
    const method = account.snapshot.metodos.find(method => !(draft.pagos || []).some(payment => Number(payment.idMetodo) === Number(method.idMetodo)));
    if (!method) return message('Ya están agregados todos los métodos de pago.');
    (draft.pagos ||= []).push({ idMetodo: Number(method.idMetodo), monto: Math.max(0, total() - (draft.pagos || []).reduce((sum, payment) => sum + payment.monto, 0)) });
    save(); renderPayments();
});
$('cash').addEventListener('input', () => { draft.recibido = $('cash').value; save(); paymentSummary(); });
document.querySelectorAll('[data-bill]').forEach(button => button.addEventListener('click', () => {
    draft.recibido = Number(draft.recibido || 0) + Number(button.dataset.bill); save(); renderPayments();
}));
$('exact-cash').addEventListener('click', () => { draft.recibido = paymentSummary().efectivo; save(); renderPayments(); });
$('sale-form').addEventListener('submit', async event => {
    event.preventDefault();
    const summary = paymentSummary();
    if (!draft.carrito.length || !summary.valido) return message(summary.error || 'Agrega productos antes de cobrar.');
    if (draft.pending) return message('Hay un cobro online sin confirmar. Reinténtalo desde el POS antes de crear otra venta.');
    confirmation = { tipo: 'venta', datos: {
        idTipo: account.snapshot.tipoVenta, rutCliente: draft.cliente || null,
        productos: draft.carrito.map(({ id, cantidad, precio }) => ({ id, cantidad, precio })),
        pagos: draft.pagos.map(({ idMetodo, monto }) => ({ idMetodo, monto })),
        efectivoRecibido: summary.efectivo > 0 ? Number(draft.recibido) : null,
    } };
    $('confirm-details').textContent = draft.carrito.map(item => `${item.cantidad} × ${item.nombre}: ${money(item.cantidad * item.precio)}`).join('\n') +
        `\nTotal: ${money(total())}\n` + payments().map(payment => `${account.snapshot.metodos.find(method => Number(method.idMetodo) === payment.idMetodo)?.nombre}: ${money(payment.monto)}`).join('\n') + `\nVuelto a entregar: ${money(summary.vuelto)}`;
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
    if (!Number.isSafeInteger(quantity) || quantity < 1 || (!entry && quantity > product.stock)) return message('Revisa la cantidad y el stock provisional.');
    const today = new Date();
    const localDate = `${today.getFullYear()}-${String(today.getMonth() + 1).padStart(2, '0')}-${String(today.getDate()).padStart(2, '0')}`;
    if (entry && $('expiry').value < localDate) return message('El vencimiento debe ser hoy o posterior.');
    confirmation = { tipo: $('kind').value, datos: {
        codigoProducto: product.codigo, cantidad: quantity,
        ...(entry ? { idProveedor: Number($('supplier').value) || null, precioCompra: Number($('cost').value), fechaVencimiento: $('expiry').value } : { motivo: $('reason').value }),
    } };
    $('confirm-details').textContent = `${entry ? 'Ingreso de mercadería' : $('reason').value}\n${product.nombre}: ${quantity} unidades` + (entry ? `\nPrecio de compra: ${money($('cost').value)}\nVencimiento: ${$('expiry').value}\nProveedor: ${$('supplier').selectedOptions[0].textContent}` : '');
    $('confirm-dialog').showModal();
});
$('confirm-dialog').addEventListener('close', async () => {
    if ($('confirm-dialog').returnValue !== 'confirm' || !confirmation || busy) return;
    busy = true;
    try {
        await writes;
        const row = await store.enqueue(user, { ...confirmation, id: crypto.randomUUID(), usuario: user, fechaLocal: new Date().toISOString() });
        message('Operación guardada en este equipo: ' + row.operation.id + '. Pendiente de sincronización.');
    } catch (error) { message(error.message); }
    finally { confirmation = null; busy = false; await refresh(); }
    if (navigator.onLine) await synchronize();
});
document.querySelectorAll('[data-tab]').forEach(button => button.addEventListener('click', () => {
    document.querySelectorAll('.tab').forEach(tab => tab.hidden = tab.id !== button.dataset.tab);
    document.querySelectorAll('[data-tab]').forEach(tab => tab.setAttribute('aria-pressed', String(tab === button)));
}));
$('export').addEventListener('click', async () => {
    const saved = await store.account(user);
    const url = URL.createObjectURL(new Blob([JSON.stringify({ usuario: user, operaciones: saved.operations, borrador: saved.draft }, null, 2)], { type: 'application/json' }));
    const link = document.createElement('a'); link.href = url; link.download = 'congelandia-movimientos-' + user + '.json'; link.click();
    setTimeout(() => URL.revokeObjectURL(url), 1000);
});
window.addEventListener('beforeinstallprompt', event => { event.preventDefault(); installPrompt = event; $('install').hidden = false; });
$('install').addEventListener('click', async () => { await installPrompt?.prompt(); $('install').hidden = true; });
window.addEventListener('online', () => { refresh().then(synchronize).catch(error => message(error.message)); });
window.addEventListener('offline', () => refresh().catch(error => message(error.message)));
onLocalChange(() => { if (!busy && !$('confirm-dialog').open) refresh().catch(error => message(error.message)); });
// Si otro usuario inició sesión desde el sitio público, asociar su identidad
// antes de mostrar cualquier copia. Un corte de red conserva la última conocida.
(async () => {
    if (navigator.onLine) {
        try { const session = await serverSession(); await store.bind(session.usuario); }
        catch (error) { serverUnavailable = error.code === 'network'; }
    }
    await refresh();
    if (navigator.onLine && user && account?.snapshot) await synchronize();
})().catch(error => message(error.message));
