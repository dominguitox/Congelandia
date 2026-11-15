const TYPES = new Set(['Nueva función', 'Mejora', 'Corrección']);

export function parseEditorialChanges(value) {
    const changes = String(value ?? '')
        .split(/\r?\n/)
        .map((line) => line.trim())
        .filter(Boolean)
        .map((line) => {
            const match = line.match(/^(Nueva función|Mejora|Corrección):\s*(.+)$/u);
            if (!match || !match[2].trim()) {
                throw new Error('Cada cambio debe ocupar una línea y comenzar con "Nueva función:", "Mejora:" o "Corrección:".');
            }
            return { tipo: match[1], descripcion: match[2].trim() };
        });

    if (!changes.length) {
        throw new Error('Debes indicar al menos un cambio editorial para la Release.');
    }

    return changes;
}

export function composeReleaseNotes(version, preparedAt, novedades) {
    const date = new Date(preparedAt);
    if (Number.isNaN(date.getTime())) throw new Error('La fecha de preparación de la entrega es inválida.');
    if (!novedades || !Array.isArray(novedades.cambios) || novedades.cambios.length === 0) {
        throw new Error('Faltan cambios editoriales válidos para las notas de la Release.');
    }
    const summary = String(novedades.resumen_cliente ?? '').trim();
    if (!summary) throw new Error('Debes escribir el resumen para el cliente antes de preparar la Release.');
    for (const item of novedades.cambios) {
        if (!TYPES.has(item.tipo) || typeof item.descripcion !== 'string' || !item.descripcion.trim()) {
            throw new Error('Las notas contienen un cambio inválido. Usa Nueva función, Mejora o Corrección y una descripción concreta.');
        }
    }

    const dateLabel = date.toISOString().slice(0, 10);
    const changes = novedades.cambios.map(({ tipo, descripcion }) => `- **${tipo}:** ${descripcion.trim()}`).join('\n');
    return `# Congelandia ${version} — ${dateLabel}\n\n## Cambios\n${changes}\n\n## Para ti\n${summary}`;
}
