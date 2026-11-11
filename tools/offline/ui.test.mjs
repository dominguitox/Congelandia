import { test } from 'node:test';
import assert from 'node:assert/strict';
import { responseError, transferState } from '../../resources/js/offline/ui.js';

test('con pendientes se habilita enviar y se bloquea descargar para no sobrescribir la copia', () => {
    const state = transferState({ snapshot: {}, operations: [{ status: 'pendiente' }] }, true);
    assert.equal(state.downloadDisabled, true);
    assert.equal(state.uploadDisabled, false);
    assert.equal(state.uploadLabel, 'Enviar pendientes (1)');
    assert.match(state.downloadHelp, /Primero envía/);
});

test('sin pendientes se permite descargar y enviar queda deshabilitado', () => {
    const state = transferState({ operations: [{ status: 'sincronizada' }] }, true);
    assert.equal(state.downloadDisabled, false);
    assert.equal(state.uploadDisabled, true);
    assert.equal(state.count, 0);
});

test('sin internet se conservan pendientes y ambos botones explican por qué esperan', () => {
    const state = transferState({ operations: [{ status: 'pendiente' }] }, false);
    assert.equal(state.downloadDisabled, true);
    assert.equal(state.uploadDisabled, true);
    assert.match(state.uploadHelp, /guardado aquí/);
});

test('un conflicto bloquea el envío y una sesión vencida permite reintentar después de entrar', () => {
    assert.equal(transferState({ operations: [{ status: 'conflicto' }] }, true).uploadDisabled, true);
    assert.equal(transferState({ operations: [{ status: 'sesion' }] }, true).uploadDisabled, false);
});

test('fallos del servidor no muestran SQL ni detalles internos', () => {
    const message = responseError(500, { message: "SQLSTATE[42S02] password=secret database=private" });
    assert.match(message, /guardados en este equipo/);
    assert.doesNotMatch(message, /SQLSTATE|secret|private/);
    assert.match(responseError(503, { code: 'offline_schema_missing' }), /actualizar su base de datos/);
});
