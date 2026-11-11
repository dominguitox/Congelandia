import { localStore, onLocalChange, pending } from './store.js';
import { prepareOffline, syncQueue } from './sync.js';
import { registerShell } from './register.js';

const panel = document.getElementById('offline-panel');
if (panel) {
    const store = localStore();
    const user = Number(panel.dataset.user);
    const status = panel.querySelector('[data-offline-status]');
    const tell = text => { status.textContent = text; };
    let running = false;
    async function update() {
        const account = await store.account(user);
        tell(`${navigator.onLine ? 'Red disponible' : 'Sin conexión'} · ${pending(account).length} pendientes · ${account?.snapshot ? 'Copia del ' + new Date(account.snapshot.actualizado).toLocaleString('es-CL') : 'Copia local sin preparar'}. Stock local provisional.`);
    }
    async function sync() {
        if (running) return;
        running = true;
        try { await syncQueue(store, user); await update(); }
        catch (error) { tell(error.message); }
        finally { running = false; }
    }
    panel.querySelector('[data-offline-prepare]').addEventListener('click', async () => {
        if (running) return;
        running = true;
        try {
            if (import.meta.env.DEV) throw new Error('La instalación offline necesita los recursos compilados. Ejecuta la compilación y abre el sistema sin Vite de desarrollo.');
            await prepareOffline(store, user);
            await registerShell();
            await update();
            tell(status.textContent + ' Recursos preparados. Abre Trabajo offline para continuar durante un corte.');
        } catch (error) { tell(error.message); }
        finally { running = false; }
    });
    panel.querySelector('[data-offline-sync]').addEventListener('click', sync);
    window.addEventListener('online', sync);
    window.addEventListener('offline', () => update().catch(error => tell(error.message)));
    onLocalChange(() => update().catch(error => tell(error.message)));
    store.bind(user).then(update).then(sync).catch(error => tell(error.message));
    // Cerrar sesión retira la asociación activa, pero conserva TODAS las colas.
    document.querySelectorAll('form[action$="/logout"]').forEach(form => form.addEventListener('submit', event => {
        if (form.dataset.localUnbound) return;
        event.preventDefault();
        store.bind(null).then(() => { form.dataset.localUnbound = '1'; form.requestSubmit(); }).catch(error => tell(error.message));
    }));
}
