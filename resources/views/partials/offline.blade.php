<aside id="offline-panel" data-user="{{ auth()->id() }}" class="alert alert-info m-3" aria-label="Trabajo offline">
    <strong>Trabajo offline provisional</strong>
    <p data-offline-status role="status" aria-live="polite">Comprobando copia local…</p>
    <p>El primer uso requiere iniciar sesión con internet y preparar la copia en este equipo.</p>
    <div class="d-flex gap-2 flex-wrap">
        <button type="button" class="btn btn-sm btn-outline-primary" data-offline-prepare>Preparar / actualizar copia</button>
        <button type="button" class="btn btn-sm btn-outline-primary" data-offline-sync>Sincronizar / reintentar</button>
        <a href="/offline" class="btn btn-sm btn-primary">Abrir trabajo offline</a>
    </div>
</aside>
