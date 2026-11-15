const SEEN_VERSION = 'congelandia:version-cargada';
const validVersion = value => typeof value === 'string' && /^(0|[1-9]\d*)\.(0|[1-9]\d*)\.(0|[1-9]\d*)$/.test(value);

export function initializeUpdateNotice({ document, window, navigator, storage, sessionStorage, fetch }) {
    const button = document.querySelector('[data-app-update]');
    if (!button || button.dataset.initialized) return;
    button.dataset.initialized = 'true';
    const current = button.dataset.currentVersion;
    let latest = null;
    let baseline;
    try { baseline = storage.getItem(SEEN_VERSION); } catch { return; }
    if (!validVersion(baseline) && validVersion(current)) {
        try { storage.setItem(SEEN_VERSION, current); } catch { return; }
        baseline = current;
    }

    const compare = value => {
        if (!validVersion(value) || !validVersion(baseline) || value === baseline) return;
        latest = value;
        button.hidden = false;
    };
    compare(current);
    const check = async () => {
        if (!navigator.onLine) return;
        try {
            const response = await fetch(button.dataset.versionUrl, { cache: 'no-store', headers: { Accept: 'application/json' } });
            if (response.ok) compare((await response.json()).numero);
        } catch { /* Se vuelve a intentar cuando haya conexión. */ }
    };
    window.addEventListener('focus', check);
    window.addEventListener('online', check);
    window.setInterval(check, 60000);
    button.addEventListener('click', async () => {
        if (!latest || button.disabled) return;
        button.disabled = true;
        try {
            const registration = await navigator.serviceWorker?.getRegistration('/');
            if (registration) await registration.update();
        } catch { /* La recarga en línea sigue disponible. */ }
        try { storage.setItem(SEEN_VERSION, latest); sessionStorage.setItem('congelandia:abrir-novedades', latest); } catch { /* Recargar también funciona sin almacenamiento. */ }
        window.location.reload();
    });
    check();
}
