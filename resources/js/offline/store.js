const DATABASE = 'congelandia-offline-v1';
const channel = typeof BroadcastChannel !== 'undefined' ? new BroadcastChannel(DATABASE) : null;
channel?.unref?.();
let connection;

export function openDatabase(factory = globalThis.indexedDB, name = DATABASE) {
    return new Promise((resolve, reject) => {
        if (!factory) return reject(new Error('Este navegador no permite IndexedDB. No cierres un cobro sin confirmar.'));
        const request = factory.open(name, 1);
        request.onupgradeneeded = () => request.result.createObjectStore('state');
        request.onsuccess = () => resolve(request.result);
        request.onerror = () => reject(request.error);
        request.onblocked = () => reject(new Error('Cierra las otras pestañas para actualizar el almacenamiento offline.'));
    });
}

export class OfflineStore {
    constructor(database) { this.database = database; }

    async read(key) {
        const db = await this.database;
        return new Promise((resolve, reject) => {
            const tx = db.transaction('state', 'readonly');
            const request = tx.objectStore('state').get(key);
            tx.oncomplete = () => resolve(request.result);
            tx.onerror = () => reject(tx.error);
        });
    }

    async bind(user) {
        const db = await this.database;
        return new Promise((resolve, reject) => {
            const tx = db.transaction('state', 'readwrite');
            tx.objectStore('state').put(user === null ? null : Number(user), 'active');
            tx.oncomplete = () => { channel?.postMessage('changed'); resolve(); };
            tx.onerror = () => reject(tx.error);
        });
    }

    async account(user) { return this.read('account:' + Number(user)); }

    // La lectura, validación, cola, stock y limpieza del borrador se confirman
    // juntos. Dos pestañas nunca pueden descontar el mismo stock local a la vez.
    async mutate(user, change) {
        const db = await this.database;
        return new Promise((resolve, reject) => {
            const tx = db.transaction('state', 'readwrite');
            const store = tx.objectStore('state');
            const active = store.get('active');
            const request = store.get('account:' + Number(user));
            let result;
            let failure;
            request.onsuccess = () => {
                try {
                    if (Number(active.result) !== Number(user)) throw new Error('Inicia sesión con el usuario de este equipo antes de continuar.');
                    const account = request.result || { user: Number(user), operations: [], draft: { carrito: [] } };
                    result = change(account);
                    store.put(account, 'account:' + Number(user));
                } catch (error) { failure = error; tx.abort(); }
            };
            tx.oncomplete = () => { channel?.postMessage('changed'); resolve(result); };
            tx.onabort = () => reject(failure || tx.error || new Error('No se pudo guardar. No se confirmó la operación.'));
            tx.onerror = () => reject(tx.error);
        });
    }

    async prepare(user, snapshot) {
        return this.mutate(user, account => {
            if (pending(account).length) throw new Error('Sincroniza todos los movimientos antes de actualizar la copia local.');
            if (account.draft?.pending) throw new Error('Confirma el cobro online pendiente desde el POS antes de preparar otra copia.');
            if (Number(snapshot.usuario.id) !== Number(user)) throw new Error('La copia pertenece a otro usuario.');
            account.snapshot = snapshot;
        });
    }

    async saveDraft(user, draft) {
        return this.mutate(user, account => { account.draft = structuredClone(draft); });
    }

    async enqueue(user, operation) {
        return this.mutate(user, account => {
            const same = account.operations.find(row => row.operation.id === operation.id);
            if (same) {
                if (JSON.stringify(same.operation) !== JSON.stringify(operation)) throw new Error('La clave ya pertenece a otra operación.');
                return same;
            }
            if (!account.snapshot) throw new Error('Prepara el modo offline con conexión antes de registrar movimientos.');
            if (Number(operation.usuario) !== Number(user)) throw new Error('La operación pertenece a otro usuario.');
            if (!['venta', 'ingreso', 'merma'].includes(operation.tipo)) throw new Error('Movimiento no permitido.');
            const lines = operation.tipo === 'venta' ? operation.datos.productos : [{
                id: operation.datos.codigoProducto, cantidad: operation.datos.cantidad,
            }];
            const seen = new Set();
            for (const line of lines) {
                const product = account.snapshot.productos.find(item => item.codigo === line.id);
                if (!product || seen.has(line.id) || !Number.isSafeInteger(line.cantidad) || line.cantidad < 1) throw new Error('Revisa los productos y cantidades.');
                seen.add(line.id);
                if (operation.tipo !== 'ingreso' && line.cantidad > product.stock) throw new Error('La cantidad supera el stock provisional de ' + product.nombre + '.');
                if (operation.tipo === 'venta' && (product.precio === null || line.precio !== product.precio)) throw new Error('El precio del carrito difiere de la copia local. Conserva el borrador y revisa el producto.');
                line.stock = product.stock;
                if (operation.tipo !== 'venta') operation.datos.stock = product.stock;
                product.stock += operation.tipo === 'ingreso' ? line.cantidad : -line.cantidad;
            }
            const row = { operation: structuredClone(operation), status: 'pendiente', message: '', response: null };
            account.operations.push(row);
            if (operation.tipo === 'venta') account.draft = { carrito: [] };
            else delete account.draft.movimiento;
            return row;
        });
    }

    async result(user, id, status, message, response = null) {
        return this.mutate(user, account => {
            const row = account.operations.find(item => item.operation.id === id);
            if (!row) throw new Error('No se encontró la operación local.');
            // Una respuesta tardía de otra pestaña no revierte una confirmación.
            if (row.status === 'sincronizada') return;
            row.status = status; row.message = message; row.response = response;
        });
    }
}

export const pending = account => (account?.operations || []).filter(row => row.status !== 'sincronizada');
export function localStore() {
    connection ??= openDatabase();
    return new OfflineStore(connection);
}
export function onLocalChange(callback) { channel?.addEventListener('message', callback); }
