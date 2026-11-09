import test from 'node:test';
import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import { argbFromHex, lstarFromArgb, Contrast } from '@material/material-color-utilities';
import { generateMd3 } from './generate-md3.mjs';

const generated = generateMd3();
const contrast = (a, b) => Contrast.ratioOfTones(lstarFromArgb(argbFromHex(a)), lstarFromArgb(argbFromHex(b)));

test('the committed palettes match the official generator and current brand seeds', () => {
    assert.equal(readFileSync(new URL('../../resources/css/theme/md3-colors.css', import.meta.url), 'utf8'), generated);
});

for (const name of ['base', 'esmeralda', 'aurora', 'coral']) {
    test(`${name}: text and container pairs meet WCAG AA contrast`, () => {
        const block = generated.match(new RegExp(`data-color="${name}"\\] \\{([^}]+)\\}`))?.[1];
        assert.ok(block, `Missing palette ${name}`);
        const colors = Object.fromEntries([...block.matchAll(/--md-sys-color-([a-z-]+): (#[a-f0-9]{6});/g)].map((match) => [match[1], match[2]]));
        for (const role of ['primary', 'secondary', 'tertiary', 'error', 'success', 'warning']) {
            assert.ok(contrast(colors[role], colors[`on-${role}`]) >= 4.5, `${role} contrast`);
            assert.ok(contrast(colors[`${role}-container`], colors[`on-${role}-container`]) >= 4.5, `${role} container contrast`);
        }
        for (const surface of ['surface', 'surface-container-lowest', 'surface-container-low', 'surface-container', 'surface-container-high', 'surface-container-highest']) {
            assert.ok(contrast(colors[surface], colors['on-surface']) >= 4.5, `${surface} text contrast`);
            assert.ok(contrast(colors[surface], colors['on-surface-variant']) >= 4.5, `${surface} secondary text contrast`);
        }
    });
}
