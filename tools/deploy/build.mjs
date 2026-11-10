import { cpSync, existsSync, mkdirSync, readFileSync, readdirSync, writeFileSync } from 'node:fs';
import { isAbsolute, join, relative, resolve, sep } from 'node:path';
import { pathToFileURL } from 'node:url';

export function normalizeVersion(value) {
    const version = String(value ?? '').trim().replace(/^v/, '');
    if (!/^(0|[1-9]\d*)\.(0|[1-9]\d*)\.(0|[1-9]\d*)$/.test(version)) {
        throw new Error('Usa una versión como 1.2.0 o v1.2.0.');
    }
    return version;
}

export function buildDeployment({ source, destination, version, commit, notes, previousChangelog = '', publicDirectory = 'public_html', builtAt = new Date().toISOString() }) {
    source = resolve(source);
    destination = resolve(destination);
    version = normalizeVersion(version);
    if (!['public', 'public_html'].includes(publicDirectory)) throw new Error('Carpeta pública inválida.');
    if (!/^[a-f0-9]{40}$/.test(commit)) throw new Error('Commit de origen inválido.');
    // Nunca escribir encima del proyecto ni de otro paquete ya existente.
    const sourceInsideDestination = relative(destination, source);
    if (sourceInsideDestination === '' || (!sourceInsideDestination.startsWith(`..${sep}`) && sourceInsideDestination !== '..' && !isAbsolute(sourceInsideDestination))) {
        throw new Error('El destino no puede contener el proyecto de origen.');
    }
    if (existsSync(destination) && readdirSync(destination).length) throw new Error('El destino debe estar vacío.');
    for (const file of ['vendor/autoload.php', 'public/build/manifest.json']) {
        if (!existsSync(join(source, file))) throw new Error(`Falta preparar ${file}.`);
    }
    const manifest = JSON.parse(readFileSync(join(source, 'public/build/manifest.json'), 'utf8'));
    for (const entry of Object.values(manifest)) {
        for (const asset of [entry.file, ...(entry.css ?? []), ...(entry.assets ?? [])].filter(Boolean)) {
            if (asset.includes('..') || isAbsolute(asset) || !existsSync(join(source, 'public/build', asset))) {
                throw new Error(`Falta un archivo del manifiesto: ${asset}.`);
            }
        }
    }
    mkdirSync(destination, { recursive: true });
    const copy = (from, to = from, filter = () => true) => cpSync(join(source, from), join(destination, to), {
        recursive: true,
        filter: (file) => !relative(source, file).split(sep).includes('.git') && filter(file),
    });

    for (const directory of ['app', 'config', 'resources', 'routes', 'vendor']) copy(directory);
    copy('bootstrap', 'bootstrap', (file) => {
        const path = relative(join(source, 'bootstrap/cache'), file);
        return path === '' || path === '.gitignore' || path.startsWith('..');
    });
    for (const directory of ['migrations', 'seeders', 'factories']) {
        if (existsSync(join(source, 'database', directory))) copy(`database/${directory}`);
    }
    copy('public', publicDirectory, (file) => {
        const path = relative(join(source, 'public'), file).split(sep).join('/');
        if (['hot', 'fonts-manifest.dev.json', 'storage'].some((name) => path === name || path.startsWith(`${name}/`))) return false;
        if (path.startsWith('images/')) return ['brand', 'hero'].includes(path.split('/')[1]);
        return true;
    });
    for (const file of ['artisan', 'composer.json', 'composer.lock']) copy(file);
    copy('tools/deploy/production.env.example', '.env.example');

    for (const directory of ['storage/app/private', 'storage/app/public', 'storage/framework/cache/data', 'storage/framework/sessions', 'storage/framework/views', 'storage/logs', 'bootstrap/cache']) {
        mkdirSync(join(destination, directory), { recursive: true });
        writeFileSync(join(destination, directory, '.gitignore'), '*\n!.gitignore\n');
    }
    writeFileSync(join(destination, '.gitignore'), `.env\n.env.*\n!.env.example\n/storage/**\n/${publicDirectory}/images/productos/\n/${publicDirectory}/storage/\n/${publicDirectory}/hot\n/bootstrap/cache/*.php\n`);
    const release = {
        numero: version,
        comentarios: notes.trim(),
        fecha_creacion: builtAt,
        commit,
        public_directory: publicDirectory,
    };
    writeFileSync(join(destination, 'release.json'), `${JSON.stringify(release, null, 2)}\n`);
    writeFileSync(join(destination, 'CHANGELOG.md'), `# Historial de publicaciones\n\n## ${version} — ${builtAt.slice(0, 10)}\n\n${notes.trim()}\n\n${previousChangelog.replace(/^# Historial de publicaciones\s*/, '').trim()}\n`);
    return release;
}

if (process.argv[1] && import.meta.url === pathToFileURL(resolve(process.argv[1])).href) {
    buildDeployment({
        source: process.cwd(),
        destination: process.env.PACKAGE_DIR,
        version: process.env.VERSION,
        commit: process.env.SOURCE_COMMIT,
        notes: readFileSync(process.env.NOTES_FILE, 'utf8'),
        previousChangelog: readFileSync(process.env.PREVIOUS_CHANGELOG_FILE, 'utf8'),
        publicDirectory: process.env.PUBLIC_DIRECTORY,
    });
}
