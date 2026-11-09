import assert from 'node:assert/strict';
import { existsSync, mkdirSync, mkdtempSync, readFileSync, rmSync, writeFileSync } from 'node:fs';
import { tmpdir } from 'node:os';
import { dirname, join } from 'node:path';
import { test } from 'node:test';
import { buildDeployment, normalizeVersion } from './build.mjs';

function fixture(t) {
    const root = mkdtempSync(join(tmpdir(), 'congelandia-deploy-test-'));
    t.after(() => rmSync(root, { recursive: true, force: true }));
    const source = join(root, 'source');
    const write = (path, contents = 'fixture') => {
        mkdirSync(dirname(join(source, path)), { recursive: true });
        writeFileSync(join(source, path), contents);
    };
    for (const file of ['app/Models/Version.php', 'config/app.php', 'resources/views/example.blade.php', 'routes/web.php', 'bootstrap/app.php', 'vendor/autoload.php', 'artisan', 'composer.json', 'composer.lock', 'public/index.php', 'public/.htaccess', 'public/build/assets/app-123.css', 'public/images/brand/logo.webp', 'public/images/hero/photo.webp', 'database/migrations/create.php', 'database/seeders/DatabaseSeeder.php', 'tools/deploy/production.env.example']) write(file);
    write('public/build/manifest.json', JSON.stringify({ 'resources/css/app.css': { file: 'assets/app-123.css' } }));
    for (const secret of ['.env', 'auth.json', 'database/database.sqlite', 'database/sql/datos.sql', 'public/hot', 'public/fonts-manifest.dev.json', 'public/images/productos/customer.jpg', 'public/storage/private.jpg', 'bootstrap/cache/config.php', 'bootstrap/cache/services.php', 'storage/logs/laravel.log', 'storage/framework/sessions/session', 'node_modules/private.txt', '.github/workflows/deploy.yml']) write(secret, 'DO NOT SHIP');
    return {
        source, write,
        options: { source, destination: join(root, 'package'), version: 'v1.2.0', commit: 'a'.repeat(40), notes: 'Reporte nuevo', builtAt: '2026-11-08T12:00:00.000Z' },
    };
}

for (const publicDirectory of ['public', 'public_html']) {
    test(`entrega completa en ${publicDirectory}, sin secretos ni datos locales`, (t) => {
        const { options } = fixture(t);
        buildDeployment({ ...options, publicDirectory });
        const included = ['vendor/autoload.php', `${publicDirectory}/build/manifest.json`, `${publicDirectory}/build/assets/app-123.css`, `${publicDirectory}/images/brand/logo.webp`, `${publicDirectory}/.htaccess`, 'storage/framework/views/.gitignore', 'bootstrap/cache/.gitignore', '.env.example', 'release.json'];
        for (const file of included) assert.ok(existsSync(join(options.destination, file)), file);
        const excluded = ['.env', 'auth.json', 'database/database.sqlite', 'database/sql', `${publicDirectory}/hot`, `${publicDirectory}/fonts-manifest.dev.json`, `${publicDirectory}/images/productos`, `${publicDirectory}/storage`, 'bootstrap/cache/config.php', 'bootstrap/cache/services.php', 'storage/logs/laravel.log', 'storage/framework/sessions/session', 'node_modules', '.github'];
        for (const file of excluded) assert.ok(!existsSync(join(options.destination, file)), file);
        const metadata = JSON.parse(readFileSync(join(options.destination, 'release.json')));
        assert.equal(metadata.numero, '1.2.0');
        assert.equal(metadata.commit, options.commit);
        assert.equal(metadata.public_directory, publicDirectory);
    });
}

test('mantiene las notas de publicaciones anteriores', (t) => {
    const { options } = fixture(t);
    buildDeployment({ ...options, previousChangelog: '# Historial de publicaciones\n\n## 1.1.0\n\nCorrección anterior' });
    const changelog = readFileSync(join(options.destination, 'CHANGELOG.md'), 'utf8');
    assert.ok(changelog.indexOf('## 1.2.0') < changelog.indexOf('## 1.1.0'));
    assert.match(changelog, /Corrección anterior/);
});

test('rechaza compilados incompletos y destinos que pisan archivos', (t) => {
    const { options, write } = fixture(t);
    write('public/build/manifest.json', JSON.stringify({ entry: { file: 'assets/missing.css' } }));
    assert.throws(() => buildDeployment(options), /manifiesto/);
    assert.throws(() => buildDeployment({ ...options, destination: options.source }), /origen/);
    assert.throws(() => buildDeployment({ ...options, destination: dirname(options.source) }), /origen/);
});

test('no sobrescribe un paquete existente', (t) => {
    const { options } = fixture(t);
    buildDeployment(options);
    assert.throws(() => buildDeployment(options), /vacío/);
});

test('valida las versiones usadas en etiquetas y comandos Git', () => {
    assert.equal(normalizeVersion('v1.2.3'), '1.2.3');
    for (const version of ['../main', '1.2', '1.2.3\nother', '1.2.3;echo', '01.2.3', '1.2.3-rc1']) {
        assert.throws(() => normalizeVersion(version));
    }
});
