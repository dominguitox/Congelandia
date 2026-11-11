import { pending } from './store.js';
import { responseError } from './ui.js';

export async function serverSession(fetcher = fetch, timeout = 8000) {
    let response;
    try {
        response = await fetcher('/offline/sesion', { headers: { Accept: 'application/json' }, cache: 'no-store', signal: AbortSignal.timeout(timeout) });
    } catch {
        const error = new Error('Sin conexión con el servidor. Puedes seguir trabajando con la copia local; los movimientos se conservan.');
        error.code = 'network';
        throw error;
    }
    if (!response.ok) {
        const error = new Error(responseError(response.status));
        error.code = [401, 403, 419].includes(response.status) ? 'session' : 'server';
        throw error;
    }
    const session = await response.json();
    if (!Number.isInteger(session.usuario) || session.usuario < 1 || typeof session.csrf !== 'string') throw new Error('Inicia sesión para enviar los movimientos guardados.');
    return session;
}

export async function syncQueue(store, user, fetcher = fetch, changed = () => {}) {
    // Web Locks coordina las pestañas; la UUID y la restricción única protegen
    // también navegadores sin esta API y respuestas perdidas tras un commit.
    const run = async () => {
        if (!pending(await store.account(user)).length) return { sent: 0 };
        let sent = 0;
        let session;
        try {
            session = await serverSession(fetcher);
            if (Number(session.usuario) !== Number(user)) throw new Error('La sesión pertenece a otro usuario. Inicia sesión con quien guardó la cola.');
        } catch (error) { await changed(); throw error; }
        while (true) {
            const account = await store.account(user);
            const row = pending(account)[0];
            if (!row) return { sent };
            if (row.status === 'conflicto') throw new Error('La primera operación requiere revisión. Las siguientes esperan para conservar el orden.');
            let response, data;
            try {
                response = await fetcher('/offline/sincronizar', {
                    method: 'POST', cache: 'no-store', signal: AbortSignal.timeout(30000),
                    headers: { 'Content-Type': 'application/json', Accept: 'application/json', 'X-CSRF-TOKEN': session.csrf },
                    body: JSON.stringify(row.operation),
                });
                data = await response.json();
            } catch {
                await store.result(user, row.operation.id, 'pendiente', 'Sin confirmación del servidor. Reintenta la misma operación.');
                await changed();
                const error = new Error('No se pudo confirmar la sincronización. La cola se conserva.');
                error.code = 'network';
                throw error;
            }
            if (response.ok && data.success && data.id === row.operation.id && data.estado === 'sincronizada') {
                await store.result(user, row.operation.id, 'sincronizada', 'Confirmada por el servidor.', data);
                sent++;
                await changed();
                continue;
            }
            const status = response.status === 409 ? 'conflicto' : ([401, 403, 419].includes(response.status) ? 'sesion' : 'rechazada');
            const message = responseError(response.status, data);
            const savedResponse = response.status >= 500 ? { message, code: data.code } : data;
            await store.result(user, row.operation.id, response.status >= 500 ? 'pendiente' : status, message, savedResponse);
            await changed();
            throw new Error(message);
        }
    };
    if (globalThis.navigator?.locks) return navigator.locks.request('congelandia-sync:' + user, run);
    return run();
}

export async function prepareOffline(store, user, fetcher = fetch) {
    const account = await store.account(user);
    if (pending(account).length) throw new Error('Primero envía los movimientos pendientes. Después podrás descargar datos nuevos.');
    const session = await serverSession(fetcher);
    if (Number(session.usuario) !== Number(user)) throw new Error('La sesión cambió. Vuelve a abrir el módulo.');
    const response = await fetcher('/offline/datos', { headers: { Accept: 'application/json' }, cache: 'no-store', signal: AbortSignal.timeout(30000) });
    if (!response.ok) {
        let data = {};
        try { data = await response.json(); } catch { /* Un error HTML tampoco reemplaza los datos locales. */ }
        throw new Error(responseError(response.status, data));
    }
    const snapshot = await response.json();
    if (!snapshot?.usuario || !['productos', 'metodos', 'clientes', 'proveedores'].every(key => Array.isArray(snapshot[key]))) throw new Error('La descarga llegó incompleta. Conservamos los datos anteriores; vuelve a intentarlo.');
    await store.prepare(user, snapshot);
    try { await globalThis.navigator?.storage?.persist?.(); } catch { /* La copia ya está guardada aunque el navegador rechace persistencia. */ }
}
