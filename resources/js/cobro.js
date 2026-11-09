export function calcularCobro(total, pagos, recibido) {
    const asignado = pagos.reduce((suma, pago) => suma + pago.monto, 0);
    const efectivo = pagos.filter(pago => pago.esEfectivo).reduce((suma, pago) => suma + pago.monto, 0);
    const restante = total - asignado;
    let error = '';
    if (!Number.isSafeInteger(total) || total < 0 || !pagos.length) error = 'Revisa el total y los métodos de pago.';
    else if (pagos.some(p => !p.idMetodo || !Number.isSafeInteger(p.monto) || p.monto < 0 || (total > 0 && p.monto === 0))) error = 'Ingresa un importe entero mayor que cero para cada método.';
    else if (new Set(pagos.map(p => String(p.idMetodo))).size !== pagos.length) error = 'Usa cada método de pago una sola vez.';
    else if (restante > 0) error = 'Falta asignar parte del total a los métodos de pago.';
    else if (restante < 0) error = 'Los importes aplicados superan el total de la venta.';
    else if (efectivo > 0 && (!Number.isSafeInteger(recibido) || recibido < efectivo)) error = 'El efectivo recibido no alcanza para cubrir la parte en efectivo.';
    return { valido: !error, error, asignado, restante, efectivo, vuelto: efectivo > 0 && Number.isSafeInteger(recibido) ? Math.max(0, recibido - efectivo) : 0 };
}
