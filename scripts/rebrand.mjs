// Rebrands the app: name, primary colour (light and dark theme), the colour of the OTP email, and the static error pages.
//   npm run rebrand -- --name "Nama Aplikasi" --color "#0f766e"
// Either --name or --color may be given alone. The logo (public/logo.png) and the favicon are image files: replace them by hand.
import { execFileSync } from 'node:child_process';
import { readFileSync, writeFileSync } from 'node:fs';
import { fileURLToPath } from 'node:url';
import { join } from 'node:path';

const arg = (flag) => {
    const index = process.argv.indexOf(flag);

    return index === -1 ? undefined : process.argv[index + 1];
};
const read = (path) => readFileSync(new URL(`../${path}`, import.meta.url), 'utf8');
const write = (path, content) => writeFileSync(new URL(`../${path}`, import.meta.url), content);
const done = (message) => console.log(`✓ ${message}`);

const name = arg('--name');
const color = arg('--color');

if (!name && !color) {
    console.error('Pakai: npm run rebrand -- --name "Nama Aplikasi" --color "#0f766e"');
    process.exit(1);
}

if (color && !/^#[0-9a-fA-F]{6}$/.test(color)) {
    console.error('--color harus berbentuk #rrggbb, mis. #0f766e');
    process.exit(1);
}

/** sRGB hex to OKLCH (Björn Ottosson's matrices), the colour space of the theme tokens. */
function hexToOklch(hex) {
    const [r, g, b] = [1, 3, 5].map((i) => parseInt(hex.slice(i, i + 2), 16) / 255).map((c) => (c <= 0.04045 ? c / 12.92 : ((c + 0.055) / 1.055) ** 2.4));
    const l = Math.cbrt(0.4122214708 * r + 0.5363325363 * g + 0.0514459929 * b);
    const m = Math.cbrt(0.2119034982 * r + 0.6806995451 * g + 0.1073969566 * b);
    const s = Math.cbrt(0.0883024619 * r + 0.2817188376 * g + 0.6299787005 * b);
    const L = 0.2104542553 * l + 0.793617785 * m - 0.0040720468 * s;
    const a = 1.9779984951 * l - 2.428592205 * m + 0.4505937099 * s;
    const bb = 0.0259040371 * l + 0.7827717662 * m - 0.808675766 * s;

    return { L, C: Math.hypot(a, bb), H: ((Math.atan2(bb, a) * 180) / Math.PI + 360) % 360, luminance: 0.2126 * r + 0.7152 * g + 0.0722 * b };
}

const num = (value, digits) => Number(value.toFixed(digits));
const oklch = (L, C, H) => `oklch(${num(L, 2)} ${num(C, 3)} ${Math.round(H)})`;

if (name) {
    write('resources/js/lib/brand.ts', read('resources/js/lib/brand.ts').replace(/APP_NAME = '.*'/, `APP_NAME = '${name.replace(/'/g, "\\'")}'`));
    write('resources/js/app.tsx', read('resources/js/app.tsx').replace(/\?\? '.*';/, `?? '${name.replace(/'/g, "\\'")}';`));
    write('.env.example', read('.env.example').replace(/^APP_NAME=.*$/m, `APP_NAME="${name.replace(/"/g, '')}"`));
    const slug = name
        .toLowerCase()
        .replace(/[^a-z0-9]+/g, '-')
        .replace(/^-|-$/g, '');
    write('package.json', read('package.json').replace(/"name": ".*"/, `"name": "${slug}"`));
    done(`nama aplikasi "${name}" di resources/js/lib/brand.ts, resources/js/app.tsx, .env.example, package.json (samakan APP_NAME di .env)`);
}

if (color) {
    const { L, C, H, luminance } = hexToOklch(color);
    // The first occurrence of a token is the light theme; the later ones are the dark theme (forced block and system media query).
    const setToken = (css, token, light, dark) => {
        let seen = 0;

        return css.replace(new RegExp(`--color-${token}: [^;]*;`, 'g'), () => `--color-${token}: ${seen++ === 0 ? light : dark};`);
    };
    let css = read('resources/css/app.css');

    css = setToken(css, 'primary', oklch(L, C, H), oklch(0.72, Math.min(C, 0.14), H));
    css = setToken(css, 'primary-hover', oklch(Math.max(L - 0.05, 0), C, H), oklch(0.78, Math.min(C, 0.13), H));
    css = setToken(css, 'primary-soft', oklch(0.96, Math.min(C, 0.02), H), oklch(0.28, Math.min(C, 0.06), H));
    write('resources/css/app.css', css);
    done(`warna utama ${color} -> token primary di resources/css/app.css`);

    const old = /\?\? '(#[0-9a-fA-F]{6})'/.exec(read('email/login-code.ts'))?.[1];

    if (old) {
        for (const file of ['email/login-code.ts', 'resources/views/emails/login-code.blade.php']) {
            write(file, read(file).replaceAll(old, color));
        }

        done('warna merek di template email (TypeScript dan Blade)');
    }

    if (1.05 / (luminance + 0.05) < 4.5) {
        console.warn('! Kontras teks putih di atas warna ini di bawah 4,5:1 (tombol utama sulit dibaca). Pilih warna yang lebih gelap.');
    }
}

const root = fileURLToPath(new URL('..', import.meta.url));

execFileSync(join(root, 'node_modules/.bin/tsx'), ['scripts/generate-error-pages.ts'], { cwd: root, stdio: 'ignore' });
done('halaman error statis dibuat ulang (resources/views/errors/)');
console.log('\nSelesai. Jalankan: npm run check && npm run build');
