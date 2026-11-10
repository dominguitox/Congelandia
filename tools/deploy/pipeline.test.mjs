import assert from 'node:assert/strict';
import { execFileSync } from 'node:child_process';
import { cpSync, existsSync, mkdirSync, mkdtempSync, readFileSync, rmSync, writeFileSync } from 'node:fs';
import { tmpdir } from 'node:os';
import { dirname, join } from 'node:path';
import { fileURLToPath } from 'node:url';
import { test } from 'node:test';

const scripts = dirname(fileURLToPath(import.meta.url));
const bash = process.platform === 'win32' ? join(process.env.ProgramFiles, 'Git/bin/bash.exe') : 'bash';

for (const legacy of [false, true]) {
test(`publicar, actualizar y restaurar conserva datos (deploy ${legacy ? 'antiguo' : 'nuevo'})`, (t) => {
    const root = mkdtempSync(join(tmpdir(), 'congelandia-pipeline-'));
    t.after(() => rmSync(root, { recursive: true, force: true }));
    const source = join(root, 'source');
    const remote = join(root, 'remote.git');
    const hosting = join(root, 'hosting');
    mkdirSync(source);
    mkdirSync(hosting);
    const run = (command, args, options = {}) => execFileSync(command, args, { cwd: source, encoding: 'utf8', stdio: ['ignore', 'pipe', 'pipe'], ...options }).trim();
    const git = (...args) => run('git', args);
    git('init', '--bare', remote);
    git('init', '-b', 'main');
    git('config', 'user.name', 'Deployment Test');
    git('config', 'user.email', 'deployment@example.com');
    git('config', 'commit.gpgsign', 'false');
    cpSync(scripts, join(source, 'tools/deploy'), { recursive: true });
    writeFileSync(join(source, 'application.txt'), 'initial');
    git('add', '.');
    git('commit', '-m', 'Aplicación inicial');
    git('remote', 'add', 'origin', remote);
    git('push', 'origin', 'main');
    if (legacy) {
        git('push', 'origin', 'main:deploy');
        git('fetch', 'origin');
    }

    const prepareAndPublish = (version, publicDirectory = 'public_html') => {
        const runner = join(root, `runner-${version}`);
        const packageDir = join(runner, 'package');
        mkdirSync(packageDir, { recursive: true });
        const output = join(runner, 'output');
        const notesFile = join(runner, 'notes');
        const previousFile = join(runner, 'previous');
        const commit = git('rev-parse', 'HEAD');
        const env = { ...process.env, RUNNER_TEMP: runner.replaceAll('\\', '/'), PACKAGE_DIR: packageDir.replaceAll('\\', '/'), VERSION: version, SOURCE_COMMIT: commit, COMMENTS: 'Resumen manual', PUBLIC_DIRECTORY: publicDirectory, GITHUB_OUTPUT: output, NOTES_FILE: notesFile, PREVIOUS_CHANGELOG_FILE: previousFile };
        run(process.execPath, ['tools/deploy/prepare-release.mjs'], { env });
        const release = { numero: version, commit, comentarios: readFileSync(notesFile, 'utf8'), fecha_creacion: '2026-11-08T00:00:00Z', public_directory: publicDirectory };
        writeFileSync(join(packageDir, 'release.json'), JSON.stringify(release));
        writeFileSync(join(packageDir, 'CHANGELOG.md'), `${version}\n${release.comentarios}\n${readFileSync(previousFile, 'utf8')}`);
        mkdirSync(join(packageDir, 'public_html'));
        writeFileSync(join(packageDir, 'public_html/index.php'), version);
        run(bash, ['tools/deploy/publish.sh'], { env });
        git('fetch', 'origin');
        return { env, output, packageDir, notesFile };
    };

    prepareAndPublish('1.0.0');
    assert.equal(git('rev-parse', 'v1.0.0'), git('rev-parse', 'HEAD'));
    assert.equal(git('show', 'deploy-v1.0.0:public_html/index.php'), '1.0.0');
    const checkoutHosting = () => git(`--git-dir=${remote}`, `--work-tree=${hosting}`, 'checkout', '-f', 'deploy');
    checkoutHosting();
    writeFileSync(join(hosting, '.env'), 'PRIVATE');
    mkdirSync(join(hosting, 'public_html/images/productos'), { recursive: true });
    writeFileSync(join(hosting, 'public_html/images/productos/customer.jpg'), 'CUSTOMER PHOTO');

    writeFileSync(join(source, 'application.txt'), 'updated');
    git('add', 'application.txt');
    git('commit', '-m', 'Agrega reportes');
    const second = prepareAndPublish('1.1.0');
    assert.match(readFileSync(second.notesFile, 'utf8'), /Agrega reportes/);
    assert.doesNotMatch(readFileSync(second.notesFile, 'utf8'), /Aplicación inicial/);
    assert.equal(git('rev-list', '--count', 'origin/deploy'), legacy ? '3' : '2');
    checkoutHosting();
    assert.equal(readFileSync(join(hosting, '.env'), 'utf8'), 'PRIVATE');
    assert.equal(readFileSync(join(hosting, 'public_html/images/productos/customer.jpg'), 'utf8'), 'CUSTOMER PHOTO');
    assert.equal(readFileSync(join(hosting, 'public_html/index.php'), 'utf8'), '1.1.0');

    // Una entrega existente o un cambio de layout nunca se publica por accidente.
    assert.throws(() => run(process.execPath, ['tools/deploy/prepare-release.mjs'], { env: second.env }), /ya existe/);
    assert.throws(() => run(process.execPath, ['tools/deploy/prepare-release.mjs'], { env: { ...second.env, VERSION: '1.2.0', PUBLIC_DIRECTORY: 'public' } }), /carpeta pública/);

    const restoreRoot = join(root, 'restore');
    mkdirSync(restoreRoot);
    const restoredPackage = join(restoreRoot, 'package');
    mkdirSync(restoredPackage);
    for (const file of ['release.json', 'CHANGELOG.md', 'public_html/index.php']) {
        mkdirSync(dirname(join(restoredPackage, file)), { recursive: true });
        writeFileSync(join(restoredPackage, file), execFileSync('git', ['show', `deploy-v1.0.0:${file}`], { cwd: source }));
    }
    const restoreEnv = { ...second.env, RUNNER_TEMP: restoreRoot.replaceAll('\\', '/'), PACKAGE_DIR: restoredPackage.replaceAll('\\', '/'), VERSION: '1.0.0', SOURCE_COMMIT: git('rev-parse', 'v1.0.0'), RESTORE: 'true', GITHUB_OUTPUT: join(restoreRoot, 'output') };
    run(process.execPath, ['tools/deploy/check-restore.mjs'], { env: restoreEnv });
    run(bash, ['tools/deploy/publish.sh'], { env: restoreEnv });
    git('fetch', 'origin');
    assert.equal(git('rev-list', '--count', 'origin/deploy'), legacy ? '4' : '3');
    assert.equal(git('diff', 'deploy-v1.0.0', 'origin/deploy'), '');
    checkoutHosting();
    assert.equal(readFileSync(join(hosting, 'public_html/index.php'), 'utf8'), '1.0.0');
    assert.equal(readFileSync(join(hosting, '.env'), 'utf8'), 'PRIVATE');
    assert.ok(existsSync(join(hosting, 'public_html/images/productos/customer.jpg')));
});
}
