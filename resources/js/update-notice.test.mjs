import { test } from 'node:test';
import assert from 'node:assert/strict';
import { initializeUpdateNotice } from './update-notice.js';

test('una versión nueva muestra el aviso y al actualizar pide abrir las novedades', async () => {
    const values = new Map([['congelandia:version-cargada', '1.0.0']]);
    const session = new Map();
    const store = map => ({ getItem: key => map.get(key), setItem: (key, value) => map.set(key, value) });
    const button = new EventTarget();
    button.dataset = { currentVersion: '1.1.0', versionUrl: '/version-actual' };
    button.hidden = true;
    let updates = 0;
    let reloads = 0;
    const window = { addEventListener() {}, setInterval() {}, location: { reload: () => reloads++ } };
    initializeUpdateNotice({
        document: { querySelector: () => button }, window,
        navigator: { onLine: true, serviceWorker: { getRegistration: async () => ({ update: async () => updates++ }) } },
        storage: store(values), sessionStorage: store(session),
        fetch: async () => ({ ok: true, json: async () => ({ numero: '1.1.0' }) }),
    });
    assert.equal(button.hidden, false);
    button.dispatchEvent(new Event('click'));
    await new Promise(resolve => setImmediate(resolve));
    assert.equal(updates, 1);
    assert.equal(reloads, 1);
    assert.equal(values.get('congelandia:version-cargada'), '1.1.0');
    assert.equal(session.get('congelandia:abrir-novedades'), '1.1.0');
});
