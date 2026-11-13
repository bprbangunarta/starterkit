// Builds the static error pages in resources/views/errors/ (see scripts/error-pages.ts).
//   npm run error-pages [-- "App Name"]
import { mkdirSync, readFileSync, writeFileSync } from 'node:fs';
import { fileURLToPath } from 'node:url';
import { buildErrorPages } from './error-pages';

// Relative to the repository, not to the folder the command was started in.
const root = fileURLToPath(new URL('..', import.meta.url));

// Name, logo, and colours come from the app itself, so the error pages stay in step with a rebrand.
const pages = buildErrorPages({
    brand: readFileSync(`${root}resources/js/lib/brand.ts`, 'utf8'),
    theme: readFileSync(`${root}resources/css/app.css`, 'utf8'),
    appName: process.argv[2],
});

mkdirSync(`${root}resources/views/errors`, { recursive: true });

for (const { file, html } of pages) {
    writeFileSync(`${root}resources/views/errors/${file}`, html);
}

console.log(`${pages.length} error pages written to resources/views/errors/`);
