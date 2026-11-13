// @vitest-environment node
/// <reference types="node" />
import { readdirSync, readFileSync } from 'node:fs';
import { describe, expect, it } from 'vitest';

const read = (path: string) => readFileSync(new URL(`../../../${path}`, import.meta.url), 'utf8');

/** The guideline is the page plus the parts in components/guideline (split to keep every file short), read as one text. */
const readGuide = () =>
    [
        read('resources/js/pages/ui-guideline.tsx'),
        ...readdirSync(new URL('../components/guideline', import.meta.url))
            .filter((name) => /\.tsx?$/.test(name))
            .map((name) => read(`resources/js/components/guideline/${name}`)),
    ].join('\n');

describe('style guide', () => {
    it('lists every colour token of resources/css/app.css', () => {
        const tokens = [...read('resources/css/app.css').matchAll(/--color-([a-z-]+):/g)].map((match) => match[1]);
        const guide = readGuide();

        expect(tokens.length).toBeGreaterThan(20);

        for (const token of tokens) {
            expect(guide, `token "${token}" is missing from TOKENS in components/guideline/guideline-data.ts`).toContain(`['${token}', 'bg-${token}'`);
        }
    });

    it('shows every exported UI component', () => {
        // Building blocks that are used inside other components (shown there) rather than on their own.
        const internal = new Set(['ClearButton', 'OptionPanel', 'Pagination', 'Popover', 'PopoverTrigger', 'PopoverContent', 'SortHead', 'TooltipProvider']);
        const guide = readGuide();
        const missing: string[] = [];

        for (const file of readdirSync(new URL('../components/ui', import.meta.url)).filter((name) => /\.tsx$/.test(name) && !name.includes('.test.'))) {
            for (const match of read(`resources/js/components/ui/${file}`).matchAll(/export (?:function|const) ([A-Z][a-z]\w*)/g)) {
                const name = match[1] ?? '';

                if (!internal.has(name) && !new RegExp(`\\b${name}\\b`).test(guide)) {
                    missing.push(`${file}: ${name}`);
                }
            }
        }

        expect(missing, 'UI components without an example in the guideline').toEqual([]);
    });
});
