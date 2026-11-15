@php($novedades = $version->novedadesParaCliente())

@if($novedades)
    <section class="version-notice alert alert-success m-3" data-version-notice data-version="{{ $version->numero }}" hidden role="status" aria-live="polite" aria-label="Novedades de la versión {{ $version->numero }}">
        <div class="version-notice__copy">
            <strong>Congelandia se actualizó</strong>
            <span>Versión {{ $version->numero }}</span>
        </div>
        <button
            type="button"
            class="version-notice__trigger"
            data-version-notice-open
            aria-haspopup="dialog"
            aria-controls="versionNovedadesModal"
        >Ver novedades</button>
        <button type="button" class="btn-close" data-version-notice-dismiss aria-label="Descartar novedades de la versión {{ $version->numero }}"></button>
    </section>

    <div class="modal fade" id="versionNovedadesModal" tabindex="-1" aria-labelledby="versionNovedadesTitulo" aria-describedby="versionNovedadesResumen" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h2 class="modal-title fs-5" id="versionNovedadesTitulo">Novedades de Congelandia {{ $version->numero }}</h2>
                        <p class="mb-0 text-body-secondary">Preparada el {{ \Carbon\Carbon::parse($version->fecha_creacion)->format('d/m/Y') }}</p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar novedades de la versión"></button>
                </div>
                <div class="modal-body">
                    <h3 class="fs-6">Cambios</h3>
                    <ul class="version-notice__changes">
                        @foreach($novedades['cambios'] as $cambio)
                            <li>
                                <strong>{{ $cambio['tipo'] }}:</strong>
                                <span>{{ $cambio['descripcion'] }}</span>
                            </li>
                        @endforeach
                    </ul>
                    <p class="mb-0" id="versionNovedadesResumen">{{ $novedades['resumen_cliente'] }}</p>
                </div>
            </div>
        </div>
    </div>
@endif
