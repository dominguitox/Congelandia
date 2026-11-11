import { test } from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import { runInNewContext } from 'node:vm';

function worker(fetcher = async () => ({ status: 200, body: 'network' }), failInstall = false) {
    const handlers = {}, writes = [], removed = [];
    let activated = false;
    const allowed = ['/offline', '/offline.webmanifest', '/build/assets/offline.js'];
    const shell = { body: 'generic offline shell' };
    const source = readFileSync(new URL('../../resources/js/offline/worker.js', import.meta.url), 'utf8')
        .replace('__CACHE_NAME__', JSON.stringify('congelandia-shell-new'))
        .replace('__ASSETS__', JSON.stringify(allowed));
    runInNewContext(source, {
        URL, Promise,
        self: { location: { origin: 'https://demo.test' }, addEventListener: (name, handler) => { handlers[name] = handler; }, clients: { claim: async () => {} }, skipWaiting: async () => { activated = true; } },
        caches: {
            keys: async () => ['congelandia-shell-old', 'another-application'],
            delete: async key => { removed.push(key); },
            open: async () => ({ addAll: async assets => { writes.push(...assets); if (failInstall) throw Error('missing asset'); }, match: async () => shell }),
        },
        fetch: fetcher,
    });
    function request(path, method = 'GET', mode = 'cors') {
        let response;
        handlers.fetch({ request: { url: 'https://demo.test' + path, method, mode }, respondWith: promise => { response = promise; } });
        return response;
    }
    return { handlers, writes, removed, request, allowed, shell, get activated() { return activated; } };
}

test('la instalación solo precarga la pantalla genérica y los estáticos explícitos', async () => {
    const item = worker();
    let done; item.handlers.install({ waitUntil: promise => { done = promise; } }); await done;
    assert.deepEqual(item.writes, item.allowed);
    assert.equal(item.writes.includes('/pos'), false);
    assert.equal(item.activated, true);
});

test('si falta un recurso la instalación falla en vez de prometer disponibilidad parcial', async () => {
    const item = worker(undefined, true);
    let done; item.handlers.install({ waitUntil: promise => { done = promise; } });
    await assert.rejects(done);
    assert.equal(item.activated, false);
});

test('no intercepta ni cachea datos privados, sesión, reportes o escrituras', () => {
    const item = worker();
    for (const path of ['/offline/datos', '/offline/sesion', '/reportes', '/login', '/clientes', '/venta/registrar']) {
        assert.equal(item.request(path), undefined);
    }
    assert.equal(item.request('/offline/sincronizar', 'POST'), undefined);
    assert.equal(item.request('/pos', 'POST', 'navigate'), undefined);
});

test('las navegaciones al POS e inventario usan la pantalla genérica ante fallo de red o 5xx', async () => {
    for (const fetcher of [async () => { throw Error('network'); }, async () => ({ status: 503 })]) {
        const item = worker(fetcher);
        for (const path of ['/pos', '/inventario/', '/pos?busqueda=helado']) {
            assert.equal(await item.request(path, 'GET', 'navigate'), item.shell);
        }
    }
});

test('una respuesta autenticada online pasa por red y no se almacena', async () => {
    const item = worker();
    assert.equal((await item.request('/pos', 'GET', 'navigate')).body, 'network');
    assert.equal(item.writes.length, 0);
});

test('una actualización solo elimina su caché de estáticos anterior', async () => {
    const item = worker();
    let done; item.handlers.activate({ waitUntil: promise => { done = promise; } }); await done;
    assert.deepEqual(item.removed, ['congelandia-shell-old']);
});
