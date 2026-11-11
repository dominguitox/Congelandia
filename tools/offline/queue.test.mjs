import { test } from 'node:test';
import assert from 'node:assert/strict';
import { IDBFactory } from 'fake-indexeddb';
import { OfflineStore, openDatabase, pending } from '../../resources/js/offline/store.js';
import { syncQueue } from '../../resources/js/offline/sync.js';

async function fixture() {
    const factory = new IDBFactory();
    const name = crypto.randomUUID();
    const db = await openDatabase(factory, name);
    const store = new OfflineStore(db);
    await store.bind(1);
    await store.prepare(1, { usuario: { id: 1 }, productos: [{ codigo: 'A', nombre: 'Helado', stock: 10, precio: 500 }] });
    return { factory, name, db, store };
}
function operation(kind = 'venta', quantity = 2) {
    return { id: crypto.randomUUID(), usuario: 1, fechaLocal: '2026-11-10T12:00:00.000Z', tipo: kind,
        datos: kind === 'venta' ? { productos: [{ id: 'A', cantidad: quantity, precio: 500 }], pagos: [{ idMetodo: 1, monto: quantity * 500 }] }
            : { codigoProducto: 'A', cantidad: quantity, ...(kind === 'merma' ? { motivo: 'Merma por Daño' } : { precioCompra: 100, fechaVencimiento: '2027-01-01', idProveedor: null }) } };
}
const response = (status, data) => ({ ok: status < 400, status, json: async () => data });
function sessionOr(handler) {
    return async (url, options) => url === '/offline/sesion' ? response(200, { usuario: 1, csrf: 'memory-only' }) : handler(url, options);
}

test('guarda borrador y cola en IndexedDB y los recupera tras cerrar la conexión', async () => {
    const { store, db, factory, name } = await fixture();
    await store.saveDraft(1, { carrito: [{ id: 'A', cantidad: 3, precio: 500 }], pagos: [{ idMetodo: 1, monto: 1500 }], recibido: 2000 });
    db.close();
    const reopened = new OfflineStore(await openDatabase(factory, name));
    assert.equal((await reopened.account(1)).draft.carrito[0].cantidad, 3);
    await reopened.enqueue(1, operation());
    assert.equal((await reopened.account(1)).snapshot.productos[0].stock, 8);
    assert.equal((await reopened.account(1)).draft.carrito.length, 0);
    assert.equal(pending(await reopened.account(1)).length, 1);
    (await reopened.database).close();
});

test('ingreso venta y merma conservan el orden y el stock esperado antes de cada operación', async () => {
    const { store, db } = await fixture();
    await store.enqueue(1, operation('ingreso', 5));
    await store.enqueue(1, operation('venta', 3));
    await store.enqueue(1, operation('merma', 2));
    const account = await store.account(1);
    assert.deepEqual(account.operations.map(row => row.operation.datos.stock ?? row.operation.datos.productos[0].stock), [10, 15, 12]);
    assert.equal(account.snapshot.productos[0].stock, 10);
    const sent = [];
    await syncQueue(store, 1, sessionOr(async (_, options) => {
        const op = JSON.parse(options.body); sent.push(op.id);
        return response(200, { id: op.id, success: true, estado: 'sincronizada' });
    }));
    assert.deepEqual(sent, account.operations.map(row => row.operation.id));
    assert.equal(pending(await store.account(1)).length, 0);
    assert.equal(JSON.stringify(await store.account(1)).includes('memory-only'), false);
    db.close();
});

test('dos pestañas no venden stock local por encima del disponible', async () => {
    const { store, db } = await fixture();
    const results = await Promise.allSettled([store.enqueue(1, operation('venta', 7)), store.enqueue(1, operation('merma', 7))]);
    assert.equal(results.filter(result => result.status === 'fulfilled').length, 1);
    assert.equal((await store.account(1)).snapshot.productos[0].stock, 3);
    assert.equal((await store.account(1)).operations.length, 1);
    db.close();
});

test('un fallo al descontar varias líneas revierte la transacción local completa', async () => {
    const { store, db } = await fixture();
    const op = operation(); op.datos.productos.push({ id: 'NO-EXISTE', cantidad: 1, precio: 1 });
    await assert.rejects(store.enqueue(1, op));
    assert.equal((await store.account(1)).snapshot.productos[0].stock, 10);
    assert.equal((await store.account(1)).operations.length, 0);
    db.close();
});

test('sin pendientes no consulta la sesión ni anuncia un envío inexistente', async () => {
    const { store, db } = await fixture();
    const result = await syncQueue(store, 1, async () => { throw new Error('No debe consultar la red'); });
    assert.deepEqual(result, { sent: 0 });
    db.close();
});

test('una base pendiente de actualizar conserva los originales y permite enviarlos después', async () => {
    const { store, db } = await fixture();
    await store.enqueue(1, operation('ingreso', 5));
    const original = structuredClone((await store.account(1)).operations[0].operation);
    await assert.rejects(syncQueue(store, 1, sessionOr(async () => response(503, {
        code: 'offline_schema_missing', message: 'SQLSTATE table private is missing',
    }))), /actualizar su base de datos/);
    const saved = (await store.account(1)).operations[0];
    assert.equal(saved.status, 'pendiente');
    assert.deepEqual(saved.operation, original);
    assert.doesNotMatch(JSON.stringify(saved.response), /SQLSTATE|private/);
    const result = await syncQueue(store, 1, sessionOr(async (_, options) => {
        const op = JSON.parse(options.body);
        assert.deepEqual(op, original);
        return response(200, { id: op.id, success: true, estado: 'sincronizada' });
    }));
    assert.deepEqual(result, { sent: 1 });
    assert.equal((await store.account(1)).snapshot.productos[0].stock, 15);
    db.close();
});

test('respuesta perdida y reintento conservan exactamente UUID y datos sin descontar otra vez', async () => {
    const { store, db } = await fixture();
    await store.enqueue(1, operation());
    const sent = [];
    await assert.rejects(syncQueue(store, 1, sessionOr(async (_, options) => { sent.push(options.body); throw new TypeError('network'); })));
    assert.equal(pending(await store.account(1))[0].status, 'pendiente');
    await syncQueue(store, 1, sessionOr(async (_, options) => {
        sent.push(options.body); const op = JSON.parse(options.body);
        return response(200, { id: op.id, success: true, estado: 'sincronizada', repetida: true });
    }));
    assert.equal(sent[0], sent[1]);
    assert.equal((await store.account(1)).snapshot.productos[0].stock, 8);
    db.close();
});

for (const status of [401, 403, 419, 422, 500]) test(`respuesta ${status} conserva datos y detiene las operaciones posteriores`, async () => {
    const { store, db } = await fixture();
    await store.enqueue(1, operation()); await store.enqueue(1, operation());
    const original = structuredClone((await store.account(1)).operations.map(row => row.operation));
    let sent = 0;
    await assert.rejects(syncQueue(store, 1, sessionOr(async () => { sent++; return response(status, { message: 'rechazo' }); })));
    const account = await store.account(1);
    assert.equal(sent, 1); assert.equal(pending(account).length, 2);
    assert.deepEqual(account.operations.map(row => row.operation), original);
    db.close();
});

test('conflicto queda visible e impide reenvío automático y actualización de copia', async () => {
    const { store, db } = await fixture();
    await store.enqueue(1, operation()); await store.enqueue(1, operation());
    await assert.rejects(syncQueue(store, 1, sessionOr(async () => response(409, { message: 'precio cambió', conflictos: [{ campo: 'precio' }] }))));
    let sent = 0;
    await assert.rejects(syncQueue(store, 1, sessionOr(async () => { sent++; })));
    assert.equal(sent, 0);
    await assert.rejects(store.prepare(1, { usuario: { id: 1 }, productos: [] }));
    assert.equal(pending(await store.account(1))[0].response.conflictos[0].campo, 'precio');
    db.close();
});

test('cerrar sesión o cambiar usuario conserva la cola y bloquea escrituras ajenas', async () => {
    const { store, db } = await fixture();
    await store.enqueue(1, operation());
    await store.bind(null);
    await assert.rejects(store.enqueue(1, operation()));
    await store.bind(2);
    await assert.rejects(store.enqueue(1, operation()));
    let sent = 0;
    await assert.rejects(syncQueue(store, 1, async () => { sent++; return response(200, { usuario: 2 }); }));
    assert.equal(sent, 1); assert.equal(pending(await store.account(1)).length, 1);
    assert.equal(await store.account(2), undefined);
    await store.bind(1);
    assert.equal((await store.account(1)).snapshot.productos[0].stock, 8);
    db.close();
});

test('una merma no borra el carrito de una venta en preparación', async () => {
    const { store, db } = await fixture();
    await store.saveDraft(1, { carrito: [{ id: 'A', cantidad: 1, precio: 500 }], movimiento: { kind: 'merma' } });
    await store.enqueue(1, operation('merma', 1));
    assert.equal((await store.account(1)).draft.carrito.length, 1);
    assert.equal((await store.account(1)).draft.movimiento, undefined);
    db.close();
});
