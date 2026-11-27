document.addEventListener('DOMContentLoaded', () => {
    const buttons = document.querySelectorAll('.btn-expand');
    const details = document.querySelectorAll('.detalle-row');
    buttons.forEach((button) => {
        button.addEventListener('click', () => {
            const detail = document.getElementById(button.dataset.target);
            if (!detail) return;
            const wasOpen = detail.classList.contains('detalle-visible');
            details.forEach((row) => row.classList.remove('detalle-visible'));
            buttons.forEach((other) => {
                other.classList.remove('abierto');
                other.setAttribute('aria-expanded', 'false');
            });
            if (!wasOpen) {
                detail.classList.add('detalle-visible');
                button.classList.add('abierto');
                button.setAttribute('aria-expanded', 'true');
            }
        });
    });
});
