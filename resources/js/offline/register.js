export async function registerShell() {
    if (!globalThis.isSecureContext || !navigator.serviceWorker) throw new Error('La instalación offline requiere HTTPS o localhost y un navegador compatible con Service Workers.');
    const registration = await navigator.serviceWorker.register('/offline-sw.js', { scope: '/', updateViaCache: 'none' });
    if (registration.active) return registration;
    await new Promise((resolve, reject) => {
        const worker = registration.installing || registration.waiting;
        const timeout = setTimeout(() => reject(new Error('No se completó la instalación offline. Comprueba la conexión y reintenta.')), 30000);
        const check = () => {
            if (worker?.state === 'activated') { clearTimeout(timeout); resolve(); }
            if (!worker || worker.state === 'redundant') { clearTimeout(timeout); reject(new Error('No se descargaron todos los recursos offline. Reintenta con conexión.')); }
        };
        worker?.addEventListener('statechange', check);
        check();
    });
    return registration;
}
