// @vitest-environment node
/// <reference types="node" />
import { readFileSync } from 'node:fs';
import { describe, expect, it } from 'vitest';
import { buildErrorPages } from '../../../scripts/error-pages';
import { ERROR_PAGES } from '@/lib/error-pages';

const read = (path: string) => readFileSync(new URL(`../../../${path}`, import.meta.url), 'utf8');
const pages = buildErrorPages({ brand: read('resources/js/lib/brand.ts'), theme: read('resources/css/app.css') });

describe('static error pages (resources/views/errors/*.blade.php)', () => {
    it('has a page for every status the app words', () => {
        expect(pages.map((page) => page.file)).toEqual(ERROR_PAGES.map((page) => `${page.status}.blade.php`));
    });

    it.each(pages.map((page) => [page.file, page.html] as const))(
        '%s is up to date with the words, pictures, and tokens (run `npm run error-pages`)',
        (file, html) => {
            expect(read(`resources/views/errors/${file}`)).toBe(html);
        },
    );

    it.each(pages.map((page) => [page.file, page.html] as const))(
        '%s keeps the light tokens in the page and the dark ones only for a dark system',
        (_file, html) => {
            const root = /:root\{([^}]*)\}/.exec(html)?.[1] ?? '';
            const names = [...root.matchAll(/--color-([a-z0-9-]+):/g)].map((match) => match[1]);

            expect(new Set(names).size, 'a token declared twice in the light block, so the later one wins').toBe(names.length);
            expect(root).toContain('--color-primary:oklch(0.386 0.092 243.4)');
            expect(html).toMatch(/@media\(prefers-color-scheme:dark\)\{:root\{color-scheme:dark;[^}]*--color-primary:oklch\(0\.72 0\.14 243\.4\)/);
        },
    );

    it('calls the 503 page maintenance: the title says so and the code is only a small note', () => {
        const html = pages.find((page) => page.file === '503.blade.php')?.html ?? '';

        expect(html).toContain('<title>Sedang dalam pemeliharaan - SIPEBRI</title>');
        expect(html).toContain('<h1>Sedang dalam pemeliharaan</h1>');
        expect(html).toContain('<p class="badge">Kode 503</p>');
        expect(html).not.toMatch(/service unavailable/i);
    });

    it.each(pages.map((page) => [page.file, page.html] as const))(
        '%s needs no script and no stylesheet, and no Blade syntax that would run (safe under a strict Content-Security-Policy)',
        (_file, html) => {
            expect(html).not.toMatch(/javascript:|<script|<link|\{\{|\{!!/i);
            expect(html).toContain('<img class="mark" src="/logo.png"');
            expect(html).toContain('<svg');
            expect(html).toContain('name="viewport"');
        },
    );

    it('escapes an app name typed on the command line', () => {
        const [first] = buildErrorPages({ brand: read('resources/js/lib/brand.ts'), theme: read('resources/css/app.css'), appName: 'A&B <Bank>' });

        expect(first?.html).toContain('A&amp;B &lt;Bank&gt;');
        expect(first?.html).not.toContain('<Bank>');
    });
});
