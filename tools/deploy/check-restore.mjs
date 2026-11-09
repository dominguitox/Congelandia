import { execFileSync } from 'node:child_process';
import { appendFileSync, readFileSync } from 'node:fs';
import { join } from 'node:path';
import { normalizeVersion } from './build.mjs';

const restored = JSON.parse(readFileSync(join(process.env.PACKAGE_DIR, 'release.json'), 'utf8'));
const current = JSON.parse(execFileSync('git', ['show', 'origin/deploy:release.json'], { encoding: 'utf8' }));
if (restored.public_directory !== current.public_directory) throw new Error('Las entregas usan carpetas públicas diferentes.');
const version = normalizeVersion(process.env.VERSION);
if (version !== restored.numero || !/^[a-f0-9]{40}$/.test(restored.commit)) throw new Error('Metadatos de restauración inválidos.');
appendFileSync(process.env.GITHUB_OUTPUT, `version=${version}\ncommit=${restored.commit}\n`);
