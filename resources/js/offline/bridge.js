import { localStore, onLocalChange, pending } from './store.js';
import { prepareOffline, syncQueue } from './sync.js';
import { onShellUpdate, registerShell } from './register.js';
import { transferState } from './ui.js';

const panel = document.getElementById('offline-panel');
if (panel) {
    const store = localStore();
    const user = Number(panel.dataset.user);
    const status = panel.querySelector('[data-offline-status]');
    const message = panel.querySelector('[data-offline-message]');
    const tell = text => { message.textContent = text; };
    const download = panel.querySelector('[data-offline-prepare]');
    const upload = panel.querySelector('[data-offline-sync]');
    const updateButton = panel.querySelector('[data-offline-update]');
    let activity = '';
    async function update() {
        const account = await store.account(user);
        const state = transferState(account, navigator.onLine, activity);
        status.textContent = `${navigator.onLine ? 'Con conexión' : 'Sin conexión'} · ${state.count} movimientos por enviar · ${account?.snapshot ? 'Datos descargados el ' + new Date(account.snapshot.actualizado).toLocaleString('es-CL') : 'Todavía no descargaste datos en este equipo'}.`;
        download.textContent = state.downloadLabel; download.disabled = state.downloadDisabled;
        upload.textContent = state.uploadLabel; upload.disabled = state.uploadDisabled;
        panel.querySelector('[data-offline-download-help]').textContent = state.downloadHelp;
        panel.querySelector('[data-offline-upload-help]').textContent = state.uploadHelp;
    }
    async function sync() {
        if (activity || !navigator.onLine || !pending(await store.account(user)).length) return;
        activity = 'upload'; await update();
        try { const result = await syncQueue(store, user); tell(`${result.sent} movimientos enviados.`); }
        catch (error) { tell(error.message); }
        finally { activity = ''; await update(); }
    }
    panel.querySelector('[data-offline-prepare]').addEventListener('click', async () => {
        if (activity) return;
        activity = 'download'; await update();
        try {
            if (import.meta.env.DEV) throw new Error('La instalación offline necesita los recursos compilados. Ejecuta la compilación y abre el sistema sin Vite de desarrollo.');
            await registerShell();
            await prepareOffline(store, user);
            tell('Datos descargados. Ya puedes abrir ventas e inventario sin internet.');
        } catch (error) { tell(error.message); }
        finally { activity = ''; await update(); }
    });
    panel.querySelector('[data-offline-sync]').addEventListener('click', sync);
    window.addEventListener('online', sync);
    window.addEventListener('offline', () => update().catch(error => tell(error.message)));
    onLocalChange(() => update().catch(error => tell(error.message)));
    onShellUpdate(() => { updateButton.hidden = false; });
    updateButton.addEventListener('click', async () => { await store.account(user); location.reload(); });
    store.bind(user).then(update).then(sync).catch(error => tell(error.message));
    // Cerrar sesión retira la asociación activa, pero conserva TODAS las colas.
    document.querySelectorAll('form[action$="/logout"]').forEach(form => form.addEventListener('submit', event => {
        if (form.dataset.localUnbound) return;
        event.preventDefault();
        store.bind(null).then(() => { form.dataset.localUnbound = '1'; form.requestSubmit(); }).catch(error => tell(error.message));
    }));
}
