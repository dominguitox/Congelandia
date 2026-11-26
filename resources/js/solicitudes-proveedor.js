document.querySelectorAll('.solicitud-mensaje').forEach((panel) => {
    const mensaje = panel.querySelector('textarea');
    const whatsapp = panel.querySelector('.solicitud-whatsapp');
    const correo = panel.querySelector('.solicitud-correo');
    mensaje.addEventListener('input', () => {
        const texto = encodeURIComponent(mensaje.value);
        if (whatsapp) whatsapp.href = `https://wa.me/${panel.dataset.telefono}?text=${texto}`;
        if (correo) correo.href = `mailto:${panel.dataset.correo}?subject=${encodeURIComponent('Solicitud de productos')}&body=${texto}`;
    });
});

const buscador = document.querySelector('#buscar-solicitud');
if (buscador) {
    const filas = [...document.querySelectorAll('.solicitud-fila')];
    const resultados = document.querySelector('#solicitud-resultados');
    const normalizar = (texto) => texto.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLocaleLowerCase();
    buscador.addEventListener('input', () => {
        const consulta = normalizar(buscador.value.trim());
        let visibles = 0;
        filas.forEach((fila) => {
            fila.hidden = !normalizar(fila.dataset.busqueda).includes(consulta);
            if (!fila.hidden) visibles++;
        });
        resultados.textContent = consulta ? `${visibles} productos encontrados. Las selecciones ocultas se conservan.` : '';
    });
}
