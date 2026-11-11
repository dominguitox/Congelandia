<aside id="offline-panel" data-user="{{ auth()->id() }}" class="alert alert-info m-3" aria-label="Trabajo offline">
    <strong>Trabajar sin internet</strong>
    <p data-offline-status role="status" aria-live="polite">Comprobando copia local…</p>
    <p data-offline-message role="status" aria-live="polite"></p>
    <div class="row g-3 my-1">
        <div class="col-md-6"><strong>Servidor → Este equipo</strong><p class="mb-2">Descarga los productos, precios y stock para trabajar sin conexión.</p><button type="button" class="btn btn-sm btn-outline-primary" data-offline-prepare>Descargar datos</button><p class="small mt-2 mb-0" data-offline-download-help></p></div>
        <div class="col-md-6"><strong>Este equipo → Servidor</strong><p class="mb-2">Envía las ventas, ingresos y salidas que guardaste en este equipo.</p><button type="button" class="btn btn-sm btn-primary" data-offline-sync disabled>Enviar pendientes</button><p class="small mt-2 mb-0" data-offline-upload-help></p></div>
    </div>
    <a href="/offline" class="btn btn-sm btn-outline-primary mt-2">Abrir ventas e inventario sin internet</a>
    <button type="button" class="btn btn-sm btn-outline-primary mt-2" data-offline-update hidden>Actualizar pantalla</button>
</aside>
