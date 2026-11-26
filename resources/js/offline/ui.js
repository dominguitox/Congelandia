import { pending } from './store.js';

export function compactStatus(account, online, activity = '') {
    const rows = pending(account);
    const date = new Date(account?.snapshot?.actualizado);
    const copy = account?.snapshot?.actualizado && !Number.isNaN(date.getTime())
        ? `Copia ${date.toLocaleString('es-CL', { day: '2-digit', month: '2-digit', hour: '2-digit', minute: '2-digit' })}`
        : 'Sin copia';
    const pendingText = rows.length ? `${rows.length} pendiente${rows.length === 1 ? '' : 's'}` : 'Sin pendientes';
    const connection = activity === 'upload' ? 'Enviando' : activity === 'download' ? 'Descargando' : online ? 'En línea' : 'Sin conexión';
    return `${connection} · ${pendingText} · ${copy}`;
}

export function transferState(account, online, activity = '') {
    const rows = pending(account);
    const count = rows.length;
    const conflict = rows[0]?.status === 'conflicto';
    return {
        count,
        downloadDisabled: !online || Boolean(activity) || count > 0 || Boolean(account?.draft?.pending),
        uploadDisabled: !online || Boolean(activity) || count === 0 || conflict,
        downloadLabel: activity === 'download' ? 'Descargando datos…' : 'Descargar datos',
        uploadLabel: activity === 'upload' ? 'Enviando pendientes…' : `Enviar pendientes${count ? ` (${count})` : ''}`,
        downloadHelp: count ? 'Primero envía los movimientos pendientes. Después podrás descargar datos nuevos.'
            : account?.draft?.pending ? 'Primero confirma el cobro pendiente desde el POS.'
            : !online ? 'Necesitas internet para descargar los datos del servidor.'
            : account?.snapshot ? 'Actualiza los productos, precios y stock descargados en este equipo.' : 'Hazlo una vez antes de trabajar sin internet.',
        uploadHelp: !count ? 'Todo está enviado. No hay movimientos pendientes.'
            : conflict ? 'El primer movimiento necesita revisión. Los demás siguen guardados y esperan su turno.'
            : !online ? `${count} ${count === 1 ? 'movimiento guardado aquí. Se enviará' : 'movimientos guardados aquí. Se enviarán'} cuando vuelva internet.`
            : `${count} ${count === 1 ? 'movimiento listo' : 'movimientos listos'} para enviar al servidor.`,
    };
}

export const statusLabel = status => ({ pendiente: 'Por enviar', sincronizada: 'Enviado', conflicto: 'Necesita revisión',
    sesion: 'Inicia sesión para enviar', rechazada: 'No se pudo enviar' })[status] || 'Por enviar';

export function operationTitle(operation) {
    if (operation.tipo === 'venta') return 'Venta';
    if (operation.tipo === 'ingreso') return 'Ingreso de mercadería';
    return operation.datos.motivo === 'Merma por Daño' ? 'Salida por daño' : 'Salida por merma';
}

export function responseError(status, data = {}) {
    if ([401, 403, 419].includes(status)) return 'Inicia sesión con el mismo usuario y vuelve a enviar los pendientes. Los movimientos siguen guardados en este equipo.';
    if (data.code === 'offline_schema_missing') return 'El servidor necesita actualizar su base de datos antes de recibir movimientos. Los pendientes siguen guardados en este equipo.';
    if (status >= 500) return 'El servidor no pudo recibir los movimientos. Siguen guardados en este equipo; puedes volver a enviarlos más tarde.';
    if (status === 409) return 'Este movimiento necesita revisión porque los datos del servidor son diferentes. Se conservó completo y los siguientes esperan su turno.';
    const validation = Object.values(data.errors || {}).flat().join(' ');
    return validation || data.message || data.error || 'No se pudo enviar el movimiento. Sus datos siguen guardados en este equipo.';
}
