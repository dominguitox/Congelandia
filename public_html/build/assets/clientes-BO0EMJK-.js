var e=document.getElementById(`rutasClientes`),t=(t,n)=>e.dataset[t].replace(`__RUT__`,encodeURIComponent(n));document.addEventListener(`DOMContentLoaded`,function(){let e=document.getElementById(`buscadorClientes`),t=document.getElementById(`tablaDeudas`),n=document.getElementById(`tablaPagados`),r=document.getElementById(`sinResultadosDeudas`),i=document.getElementById(`sinResultadosPagados`);e&&e.addEventListener(`input`,function(){let e=this.value.toLowerCase().trim();if(t){let n=t.querySelectorAll(`tr.fila-cliente`),i=0;n.forEach(function(t){let n=t.querySelector(`.cliente-nombre`)?.textContent.toLowerCase().trim()||``,r=t.querySelector(`.cliente-telefono`)?.textContent.toLowerCase().trim()||``,a=n.includes(e)||r.includes(e);t.style.display=a?``:`none`,a&&i++}),r&&(r.style.display=e!==``&&i===0?``:`none`)}if(n){let t=n.querySelectorAll(`tr.fila-cliente`),r=0;t.forEach(function(t){let n=t.querySelector(`.cliente-nombre`)?.textContent.toLowerCase().trim()||``,i=t.querySelector(`.cliente-telefono`)?.textContent.toLowerCase().trim()||``,a=n.includes(e)||i.includes(e);t.style.display=a?``:`none`,a&&r++}),i&&(i.style.display=e!==``&&r===0?``:`none`)}})});function n(){let e=document.getElementById(`formularioCliente`);e&&(e.style.display===`none`||e.style.display===``?e.style.display=`block`:e.style.display=`none`)}function r(e){return confirm(`¿Está seguro de eliminar al cliente "`+e+`"?

El cliente dejará de aparecer en el listado, pero su historial de ventas se conservará.`)}function i(e,n,r){let i=document.getElementById(`modalEditar`),a=document.getElementById(`formEditarCliente`),o=document.getElementById(`editarRut`),s=document.getElementById(`editarNombre`),c=document.getElementById(`editarTelefono`);o.value=e,s.value=n||``,c.value=r||``,a.action=t(`update`,e),i.style.display=`flex`,setTimeout(function(){s.focus()},100)}function a(){let e=document.getElementById(`modalEditar`);e&&(e.style.display=`none`)}async function o(e){let n=document.getElementById(`modalHistorial`),r=document.getElementById(`historialClienteNombre`),i=document.getElementById(`historialClienteRut`),a=document.getElementById(`historialClienteTelefono`),o=document.getElementById(`historialCantidadVentas`),s=document.getElementById(`historialContenido`);n.style.display=`flex`,r.textContent=`Cargando...`,i.textContent=`-`,a.textContent=`-`,o.textContent=`0`,s.innerHTML=`
        <p class="historial-cargando">
            Cargando historial...
        </p>
    `;try{let n=await fetch(t(`historial`,e));if(!n.ok)throw Error(`No fue posible obtener el historial.`);let c=await n.json();if(r.textContent=c.cliente.nombre,i.textContent=c.cliente.rutCliente,a.textContent=c.cliente.telefono||`Sin teléfono`,o.textContent=c.ventas.length,c.ventas.length===0){s.innerHTML=`
                <div class="historial-vacio">
                    Este cliente no tiene ventas registradas.
                </div>
            `;return}s.innerHTML=c.ventas.map(function(e){let t=new Date(e.fecha).toLocaleDateString(`es-CL`),n=Number(e.total).toLocaleString(`es-CL`),r=Number(e.totalPagado).toLocaleString(`es-CL`),i=Number(e.saldoPendiente).toLocaleString(`es-CL`),a=`estado-pagada`;e.estado===`Pendiente`?a=`estado-pendiente`:e.estado===`Pago parcial`&&(a=`estado-parcial`);let o=``;return o=e.detalles&&e.detalles.length>0?e.detalles.map(function(e){let t=Number(e.precioCobrado).toLocaleString(`es-CL`);return`
                                    <div class="historial-producto">

                                        <span>
                                            ${e.codigoProducto}
                                        </span>

                                        <span>
                                            x${e.cantidad}
                                        </span>

                                        <strong>
                                            $${t}
                                        </strong>

                                    </div>
                                `}).join(``):`
                        <p class="sin-detalles">
                            Sin detalle disponible
                        </p>
                    `,`
                    <div class="historial-venta">

                        <div class="historial-venta-header">

                            <div>

                                <strong>
                                    Venta #${e.idSalida}
                                </strong>

                                <span>
                                    ${t}
                                </span>

                            </div>

                            <span class="${a}">
                                ${e.estado}
                            </span>

                        </div>


                        <div class="historial-montos">

                            <div>

                                <span>
                                    Total
                                </span>

                                <strong>
                                    $${n}
                                </strong>

                            </div>


                            <div>

                                <span>
                                    Pagado
                                </span>

                                <strong class="monto-pagado">
                                    $${r}
                                </strong>

                            </div>


                            <div>

                                <span>
                                    Saldo
                                </span>

                                <strong class="monto-saldo">
                                    $${i}
                                </strong>

                            </div>

                        </div>


                        <div class="historial-productos">

                            <h5>
                                Productos
                            </h5>

                            ${o}

                        </div>

                    </div>
                `}).join(``)}catch(e){console.error(e),s.innerHTML=`
            <div class="historial-error">
                No fue posible cargar el historial del cliente.
            </div>
        `}}function s(){let e=document.getElementById(`modalHistorial`);e&&(e.style.display=`none`)}async function c(e){let n=document.getElementById(`modalPago`),r=document.getElementById(`pagoVenta`),i=document.getElementById(`pagoClienteNombre`),a=document.getElementById(`pagoResumen`),o=document.getElementById(`pagoError`);n.style.display=`flex`,r.innerHTML=`
        <option value="">
            Cargando ventas...
        </option>
    `,r.disabled=!0,a.style.display=`none`,o.style.display=`none`,o.textContent=``,document.getElementById(`montoPagado`).value=``;try{let n=await fetch(t(`historial`,e));if(!n.ok)throw Error(`No fue posible obtener las ventas del cliente.`);let a=await n.json();i.textContent=a.cliente.nombre;let s=a.ventas.filter(function(e){return Number(e.saldoPendiente)>0});if(s.length===0){r.innerHTML=`
                <option value="">
                    Este cliente no tiene deudas pendientes
                </option>
            `,r.disabled=!0,o.textContent=`No existen ventas con saldo pendiente para este cliente.`,o.style.display=`block`;return}r.innerHTML=`
            <option value="">
                Seleccione una venta
            </option>
        `,s.forEach(function(e){let t=document.createElement(`option`);t.value=e.idSalida,t.textContent=`Venta #${e.idSalida} - ${u(e.saldoPendiente)} pendiente`,t.dataset.total=e.total,t.dataset.pagado=e.totalPagado,t.dataset.saldo=e.saldoPendiente,r.appendChild(t)}),r.disabled=!1,r.onchange=function(){l()}}catch(e){console.error(e),r.innerHTML=`
            <option value="">
                Error al cargar las ventas
            </option>
        `,r.disabled=!0,o.textContent=`No fue posible cargar las ventas del cliente.`,o.style.display=`block`}}function l(){let e=document.getElementById(`pagoVenta`),t=e.options[e.selectedIndex],n=document.getElementById(`pagoResumen`),r=document.getElementById(`montoPagado`);if(!t||!t.value){n.style.display=`none`,r.removeAttribute(`max`);return}let i=Number(t.dataset.total||0),a=Number(t.dataset.pagado||0),o=Number(t.dataset.saldo||0);document.getElementById(`pagoTotalVenta`).textContent=u(i),document.getElementById(`pagoTotalPagado`).textContent=u(a),document.getElementById(`pagoSaldoPendiente`).textContent=u(o),r.max=o,r.value=``,n.style.display=`grid`}function u(e){return`$`+Number(e||0).toLocaleString(`es-CL`)}document.addEventListener(`DOMContentLoaded`,function(){let e=document.getElementById(`formRegistrarPago`);e&&e.addEventListener(`submit`,function(t){let n=document.getElementById(`pagoVenta`),r=Number(document.getElementById(`montoPagado`).value);if(!n.value){t.preventDefault(),d(`Debe seleccionar una venta.`);return}let i=n.options[n.selectedIndex],a=Number(i.dataset.saldo||0);if(r<=0){t.preventDefault(),d(`El monto debe ser mayor que cero.`);return}if(r>a){t.preventDefault(),d(`El monto no puede superar el saldo pendiente.`);return}if(!document.querySelector(`input[name="idMetodo"]:checked`)){t.preventDefault(),d(`Debe seleccionar un método de pago.`);return}e.action=e.dataset.actionTemplate.replace(`__ID_SALIDA__`,encodeURIComponent(n.value))})});function d(e){let t=document.getElementById(`pagoError`);t.textContent=e,t.style.display=`block`}function f(){let e=document.getElementById(`modalPago`);e.style.display=`none`;let t=document.getElementById(`formRegistrarPago`);t&&t.reset(),document.getElementById(`pagoResumen`).style.display=`none`,document.getElementById(`pagoError`).style.display=`none`}document.addEventListener(`keydown`,function(e){e.key===`Escape`&&(s(),f(),a())}),document.addEventListener(`click`,function(e){let t=document.getElementById(`modalHistorial`);e.target===t&&s()}),document.addEventListener(`click`,function(e){let t=document.getElementById(`modalPago`);e.target===t&&f()}),document.addEventListener(`click`,function(e){let t=document.getElementById(`modalEditar`);e.target===t&&a()}),Object.assign(window,{toggleFormularioCliente:n,confirmarEliminacionCliente:r,abrirModalEditar:i,cerrarModalEditar:a,verHistorialCliente:o,cerrarHistorial:s,abrirModalPago:c,actualizarResumenPago:l,formatearMoneda:u,mostrarErrorPago:d,cerrarModalPago:f});