import { pending } from './store.js';

export async function serverSession(fetcher = fetch) {
    let response;
    try {
        response = await fetcher('/offline/sesion', { headers: { Accept: 'application/json' }, cache: 'no-store', signal: AbortSignal.timeout(15000) });
    } catch {
        const error = new Error('Sin conexión con el servidor. Puedes seguir trabajando con la copia local; los movimientos se conservan.');
        error.code = 'network';
        throw error;
    }
    if (!response.ok) throw new Error('Vuelve a iniciar sesión con el usuario que guardó los movimientos. La cola se conserva.');
    return response.json();
}

export async function syncQueue(store, user, fetcher = fetch, changed = () => {}) {
    // Web Locks coordina las pestañas; la UUID y la restricción única protegen
    // también navegadores sin esta API y respuestas perdidas tras un commit.
    const run = async () => {
        let session;
        try {
            session = await serverSession(fetcher);
            if (Number(session.usuario) !== Number(user)) throw new Error('La sesión pertenece a otro usuario. Inicia sesión con quien guardó la cola.');
        } catch (error) { changed(); throw error; }
        while (true) {
            const account = await store.account(user);
            const row = pending(account)[0];
            if (!row) return;
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
                changed();
                const error = new Error('No se pudo confirmar la sincronización. La cola se conserva.');
                error.code = 'network';
                throw error;
            }
            if (response.ok && data.success && data.id === row.operation.id && data.estado === 'sincronizada') {
                await store.result(user, row.operation.id, 'sincronizada', 'Confirmada por el servidor.', data);
                changed();
                continue;
            }
            const status = response.status === 409 ? 'conflicto' : ([401, 403, 419].includes(response.status) ? 'sesion' : 'rechazada');
            const message = Object.values(data.errors || {}).flat().join(' ') || data.message || data.error || 'El servidor rechazó la operación.';
            await store.result(user, row.operation.id, response.status >= 500 ? 'pendiente' : status, message, data);
            changed();
            throw new Error(message + ' Se conserva la operación y se detiene la cola.');
        }
    };
    if (globalThis.navigator?.locks) return navigator.locks.request('congelandia-sync:' + user, run);
    return run();
}

export async function prepareOffline(store, user, fetcher = fetch) {
    const session = await serverSession(fetcher);
    if (Number(session.usuario) !== Number(user)) throw new Error('La sesión cambió. Vuelve a abrir el módulo.');
    const response = await fetcher('/offline/datos', { headers: { Accept: 'application/json' }, cache: 'no-store', signal: AbortSignal.timeout(30000) });
    if (!response.ok) throw new Error('No se pudo descargar la copia local.');
    await store.prepare(user, await response.json());
    return navigator.storage?.persist?.();
}
