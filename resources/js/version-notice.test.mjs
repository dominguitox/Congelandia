import assert from 'node:assert/strict';
import { test } from 'node:test';
import { initializeVersionNotice } from './version-notice.js';

class Element extends EventTarget {
    constructor(version = '') {
        super();
        this.dataset = version ? { version } : {};
        this.hidden = false;
    }
}

function setup(version, storage) {
    const notice = new Element(version);
    const trigger = new Element();
    const modalElement = new Element();
    let showCount = 0;
    const Modal = { getOrCreateInstance: () => ({ show: () => showCount++ }) };
    const elements = {
        '[data-version-notice]': notice,
        '[data-version-notice-open]': trigger,
        '#versionNovedadesModal': modalElement,
    };
    const root = { querySelector: (selector) => elements[selector] ?? null };
    initializeVersionNotice(root, storage, Modal);
    return { notice, trigger, modalElement, root, Modal, showCount: () => showCount };
}

test('descartar novedades persiste solo esa versión y una versión nueva vuelve a aparecer', () => {
    const values = new Map();
    const storage = {
        getItem: (key) => values.get(key) ?? null,
        setItem: (key, value) => values.set(key, value),
    };

    const firstPage = setup('1.2.0', storage);
    assert.equal(firstPage.notice.hidden, false);
    firstPage.trigger.dispatchEvent(new Event('click'));
    assert.equal(firstPage.showCount(), 1);
    firstPage.modalElement.dispatchEvent(new Event('hidden.bs.modal'));
    assert.equal(firstPage.notice.hidden, true);

    const reloadedPage = setup('1.2.0', storage);
    assert.equal(reloadedPage.notice.hidden, true);
    const newReleasePage = setup('1.3.0', storage);
    assert.equal(newReleasePage.notice.hidden, false);
});

test('inicializar más de una vez no duplica el evento de apertura', () => {
    const storage = { getItem: () => null, setItem: () => {} };
    const page = setup('1.2.0', storage);
    initializeVersionNotice(page.root, storage, page.Modal);
    page.trigger.dispatchEvent(new Event('click'));
    assert.equal(page.showCount(), 1);
});
