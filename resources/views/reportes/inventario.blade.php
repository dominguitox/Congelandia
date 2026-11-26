<div class="stats-grid">
    @foreach([
        ['Productos bajo stock', $datos['bajoStock']->count(), $datos['criticos'].' con 3 unidades o menos'],
        ['Reposición sugerida', $numero($datos['reposicion']).' uds.', 'Para llegar a 20 por producto'],
    ] as [$label, $valor, $detalle])
        <div class="stat-tile"><div class="stat-tile-info"><span>{{ $label }}</span><strong>{{ $valor }}</strong><small>{{ $detalle }}</small></div></div>
    @endforeach
</div>
<section class="panel solicitudes-panel">
    <div class="panel-head"><div><h2 id="solicitudes-titulo">Solicitudes a proveedores</h2><p>{{ $datos['bajoStock']->count() }} productos por revisar · Selecciona solo los que quieras solicitar.</p></div></div>
    <div class="panel-body">
        @if($datos['bajoStock']->isEmpty())
            <p>No hay productos que reponer.</p>
        @else
            <details class="solicitudes-detalle" @if($errors->any()) open @endif>
            <summary><span>Preparar solicitudes</span><span class="solicitudes-indicador" aria-hidden="true"></span></summary>
            <p>Elige un proveedor con ingresos registrados y revisa las cantidades. No se guarda ni se envía ningún pedido.</p>
            <form method="POST" action="{{ route('reportes.solicitudes') }}" aria-labelledby="solicitudes-titulo">
                @csrf
                <div class="solicitud-busqueda"><label for="buscar-solicitud">Buscar producto o código</label><input class="form-control" id="buscar-solicitud" type="search" autocomplete="off"><p id="solicitud-resultados" role="status"></p></div>
                <div class="data-table-wrap solicitud-lista" role="region" aria-label="Productos sugeridos" tabindex="0"><table class="data-table solicitud-tabla"><thead><tr><th scope="col">Producto</th><th scope="col">Stock</th><th scope="col">Proveedor</th><th scope="col">Cantidad</th></tr></thead><tbody>
                @foreach($datos['bajoStock'] as $producto)
                    @php($opciones = $datos['proveedoresPorProducto']->get($producto->codigo, collect()))
                    <tr class="solicitud-fila" data-busqueda="{{ $producto->nombre }} {{ $producto->codigo }}">
                        <td><span class="celda-principal">{{ $producto->nombre }}</span><span class="celda-sub">{{ $producto->codigo }}</span>
                        <input type="hidden" name="productos[{{ $loop->index }}][codigo]" value="{{ $producto->codigo }}">
                        </td><td>{{ $numero($producto->stock) }}</td><td>
                        <label class="visually-hidden" for="proveedor-{{ $loop->index }}">Proveedor para {{ $producto->nombre }}</label>
                        <select class="form-control" id="proveedor-{{ $loop->index }}" name="productos[{{ $loop->index }}][proveedor]">
                            <option value="">No incluir</option>
                            @if($opciones->isEmpty())<option disabled>Sin proveedor asociado</option>@endif
                            @foreach($opciones as $proveedor)
                                <option value="{{ $proveedor->idProveedor }}" @selected(old('productos.'.$loop->parent->index.'.proveedor') == $proveedor->idProveedor)>{{ $proveedor->nombre }}</option>
                            @endforeach
                        </select>
                        @if($opciones->isEmpty())<small>Sin proveedor asociado</small>@endif
                        </td><td><label class="visually-hidden" for="cantidad-{{ $loop->index }}">Cantidad de {{ $producto->nombre }}</label>
                        <input class="form-control" id="cantidad-{{ $loop->index }}" name="productos[{{ $loop->index }}][cantidad]" type="number" min="1" max="100000" required value="{{ old('productos.'.$loop->index.'.cantidad', $producto->reponer) }}">
                        </td></tr>
                @endforeach
                </tbody></table></div>
                <button class="btn-solid" type="submit">Preparar mensajes</button>
            </form>
            </details>
        @endif
    </div>
</section>
<div class="reportes-columnas">
    <section class="panel">
        <div class="panel-head"><div><h2>Prioridad de reposición</h2><p>Umbral provisional: menos de 10 unidades. Objetivo: 20.</p></div></div>
        <div class="data-table-wrap prioridad-lista" role="region" aria-label="Prioridad de reposición" tabindex="0"><table class="data-table"><thead><tr><th>Producto</th><th>Stock actual</th><th>Reponer</th></tr></thead><tbody>
            @forelse($datos['bajoStock'] as $producto)
                <tr><td><span class="celda-principal">{{ $producto->nombre }}</span><span class="celda-sub">{{ $producto->categoria }} · {{ $producto->codigo }}</span></td>
                    <td><span class="pill pill--{{ $producto->stock <= 3 ? 'danger' : 'warning' }}">{{ $producto->stock < 0 ? 'Revisar: ' : '' }}{{ $numero($producto->stock) }} uds.</span></td><td>{{ $numero($producto->reponer) }} uds.</td></tr>
            @empty
                <tr><td colspan="3"><div class="empty-state"><strong>Sin alertas de reposición</strong><p>No hay productos activos por debajo del umbral.</p></div></td></tr>
            @endforelse
        </tbody></table></div>
    </section>
</div>
