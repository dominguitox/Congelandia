const STORAGE_PREFIX = 'congelandia:novedades-descartadas:';

export function initializeVersionNotice(root, storage, Modal) {
    const notice = root.querySelector('[data-version-notice]');
    const trigger = root.querySelector('[data-version-notice-open]');
    const dismiss = root.querySelector('[data-version-notice-dismiss]');
    const modalElement = root.querySelector('#versionNovedadesModal');
    const version = notice?.dataset.version;
    if (!notice || !trigger || !dismiss || !modalElement || !version || notice.dataset.initialized === 'true') return;

    notice.dataset.initialized = 'true';
    const storageKey = `${STORAGE_PREFIX}${version}`;
    try {
        notice.hidden = storage.getItem(storageKey) === '1';
    } catch {
        notice.hidden = false;
    }

    const modal = Modal.getOrCreateInstance(modalElement);
    trigger.addEventListener('click', () => modal.show());
    dismiss.addEventListener('click', () => {
        notice.hidden = true;
        try {
            storage.setItem(storageKey, '1');
        } catch {
            // Si el navegador bloquea localStorage, el aviso solo se oculta en esta página.
        }
    });
    try {
        if (sessionStorage.getItem('congelandia:abrir-novedades') === version) {
            sessionStorage.removeItem('congelandia:abrir-novedades');
            modal.show();
        }
    } catch { /* El aviso sigue disponible si se bloquea sessionStorage. */ }
}
