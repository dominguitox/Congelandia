import { execFileSync, spawnSync } from 'node:child_process';
import { appendFileSync, writeFileSync } from 'node:fs';
import { normalizeVersion } from './build.mjs';
import { composeReleaseNotes, parseEditorialChanges } from './release-notes.mjs';

const git = (...args) => execFileSync('git', args, { encoding: 'utf8' }).trim();
const hasRef = (ref) => spawnSync('git', ['rev-parse', '--verify', '--quiet', ref]).status === 0;
const version = normalizeVersion(process.env.VERSION);
const commit = git('rev-parse', 'HEAD');
const novedades = {
    cambios: parseEditorialChanges(process.env.CHANGES),
    resumen_cliente: String(process.env.CLIENT_SUMMARY ?? '').trim(),
};
if (!novedades.resumen_cliente) {
    throw new Error('Debes escribir el resumen final para el cliente antes de preparar la Release.');
}
for (const tag of [`v${version}`, `deploy-v${version}`]) {
    if (hasRef(`refs/tags/${tag}`)) throw new Error(`La versión ${version} ya existe. Usa otro número o restaura su entrega.`);
}

let previousChangelog = '';
if (hasRef('refs/remotes/origin/deploy')) {
    const metadata = spawnSync('git', ['show', 'origin/deploy:release.json'], { encoding: 'utf8' });
    if (metadata.status === 0) {
        const previous = JSON.parse(metadata.stdout);
        const older = normalizeVersion(previous.numero).split('.').map(BigInt);
        const newer = version.split('.').map(BigInt);
        const difference = newer.findIndex((part, index) => part !== older[index]);
        if (difference === -1 || newer[difference] < older[difference]) {
            throw new Error('La nueva versión debe ser mayor que la entrega actual. Para volver atrás usa Restaurar entrega DirectAdmin.');
        }
        if (previous.public_directory !== process.env.PUBLIC_DIRECTORY) {
            throw new Error('No cambies la carpeta pública entre publicaciones del mismo hosting.');
        }
        if (spawnSync('git', ['merge-base', '--is-ancestor', previous.commit, commit]).status !== 0) {
            throw new Error('La entrega anterior no es antecesora del código elegido. Usa el flujo de restauración.');
        }
        previousChangelog = git('show', 'origin/deploy:CHANGELOG.md');
    }
}

const preparedAt = new Date().toISOString();
const notes = composeReleaseNotes(version, preparedAt, novedades);
writeFileSync(process.env.NOTES_FILE, `${notes}\n`);
writeFileSync(process.env.RELEASE_CONTENT_FILE, `${JSON.stringify(novedades, null, 2)}\n`);
writeFileSync(process.env.PREVIOUS_CHANGELOG_FILE, previousChangelog);
appendFileSync(process.env.GITHUB_OUTPUT, `version=${version}\ncommit=${commit}\nprepared_at=${preparedAt}\n`);
