// Mechanical checks of the frontend code standards (see .ai/rules/frontend-react.md). Exit code 1 when something is broken.
//   npm run standards
import { readdirSync, readFileSync, statSync } from 'node:fs';
import { join, relative } from 'node:path';
import { fileURLToPath } from 'node:url';

// fileURLToPath, not .pathname: a folder name with a space or a non-ASCII letter is percent-encoded in a URL.
const root = fileURLToPath(new URL('../resources/js', import.meta.url));

/** Wayfinder output is generated and regenerated, so it is not held to the house rules. */
const generated = new Set(['actions', 'routes', 'wayfinder']);
const files = [];

/** Stops with a plain sentence instead of a stack trace when a file can't be read. */
function unreadable(path, error) {
    console.error(
        `Tidak bisa membaca ${relative(process.cwd(), path)} (${error.code ?? error.message}). Periksa izin dan isi folder resources/js, lalu ulangi.`,
    );
    process.exit(1);
}

(function walk(dir) {
    let names;

    try {
        names = readdirSync(dir);
    } catch (error) {
        unreadable(dir, error);
    }

    for (const name of names) {
        const path = join(dir, name);

        try {
            if (statSync(path).isDirectory()) {
                if (!generated.has(name) || dir !== root) {
                    walk(path);
                }
            } else if (/\.(ts|tsx)$/.test(name)) {
                files.push(path);
            }
        } catch (error) {
            unreadable(path, error);
        }
    }
})(root);

if (files.length === 0) {
    console.error(`Tidak ada berkas .ts atau .tsx di ${relative(process.cwd(), root)}: pemeriksaan ini tidak memeriksa apa pun, jadi dihentikan.`);
    process.exit(1);
}

const sources = new Map(
    files.map((file) => {
        try {
            return [file, readFileSync(file, 'utf8')];
        } catch (error) {
            return unreadable(file, error);
        }
    }),
);

/** Tests and test setup may use literal colours and localStorage. */
const isTest = (file) => /\.test\.tsx?$|\/resources\/js\/test\//.test(file);

const rules = [
    {
        name: 'Tabel mentah dilarang: pakai components/ui/data-table.tsx (DataTable)',
        test: (line) => /<table[\s>]/.test(line),
        skip: (file) => file.endsWith('components/ui/data-table.tsx'),
    },
    {
        name: 'Warna ditulis langsung: pakai token tema di resources/css/app.css',
        test: (line) => /#[0-9a-fA-F]{3,8}\b|\b(rgb|rgba|hsl|hsla|oklch)\(/.test(line) && !/^\s*(\/\/|\*|\/\*)/.test(line),
        skip: (file) => file.endsWith('components/ui/color-input.tsx') || isTest(file),
    },
    {
        name: 'Baris judul + aksi memakai items-center, bukan items-start justify-between',
        test: (line) => /items-start justify-between/.test(line),
        skip: (file) => file.endsWith('components/ui/option-panel.tsx'),
    },
    { name: 'console.log/debugger tertinggal', test: (line) => /\bconsole\.log\(|\bdebugger\b/.test(line), skip: () => false },
    {
        name: 'Palet Tailwind untuk status dilarang: pakai token success/warning/info/danger (-soft, -line, -ink)',
        test: (line) => /\b(bg|text|border|ring)-(red|green|emerald|amber|yellow|orange|blue|sky|rose|lime|teal)-\d{2,3}\b/.test(line),
        skip: () => false,
    },
    {
        name: 'Ukuran teks sembarang dilarang: pakai text-2xs, text-xs, text-sm, atau text-base (token di resources/css/app.css)',
        test: (line) => /\btext-\[\d+(\.\d+)?(px|rem)\]/.test(line),
        skip: () => false,
    },
    {
        name: 'Lapisan layar penuh buatan sendiri dilarang: pakai Modal/ConfirmDialog (components/ui/dialog.tsx) atau Dialog Radix',
        test: (line) => /\bfixed inset-0\b/.test(line),
        skip: (file) => file.endsWith('components/ui/dialog.tsx'),
    },
    {
        name: 'Tanggal, waktu, dan angka untuk orang diformat di lib/format.ts saja (formatDate, formatDateTime, formatRelative, formatNumber, rupiah)',
        test: (line) => /from 'date-fns\/locale'|\bIntl\.(NumberFormat|DateTimeFormat)\b|\.toLocale(Date|Time)?String\(/.test(line),
        skip: (file) =>
            isTest(file) ||
            file.endsWith('lib/format.ts') ||
            file.endsWith('components/ui/date-picker.tsx') ||
            file.endsWith('components/ui/date-range-picker.tsx'),
    },
    {
        name: 'type="number"/type="tel" dilarang: kolom angka memakai MaskedInput (components/ui/masked-input.tsx), karena type="number" menerima huruf e dan tanda, dan type="tel" menerima huruf',
        test: (line) => /\btype=["']?(number|tel)\b/.test(line) || /\btype:\s*['"](number|tel)['"]/.test(line),
        skip: (file) => isTest(file),
    },
    {
        name: 'Label menu terlalu panjang: satu atau dua kata, maksimal 20 karakter, tanpa garis miring (nama lengkap ada di judul halaman)',
        test: (line) => {
            const label = /\{\s*label:\s*'([^']*)',\s*to:/.exec(line)?.[1];

            return label !== undefined && (label.length > 20 || label.includes('/') || label.trim().split(/\s+/).length > 2);
        },
        skip: (file) => !file.endsWith('layouts/menu.ts'),
    },
    {
        name: 'Tanda — dan → dilarang di komentar: pakai tanda baca biasa',
        test: (line) => /^\s*(\/\/|\/\*|\*)/.test(line) && /[—→]/.test(line),
        skip: () => false,
        inComments: true,
    },
    { name: 'Tipe any dilarang', test: (line) => /:\s*any\b|\bas any\b|<any>/.test(line), skip: () => false },
    { name: '@ts-ignore/@ts-nocheck dilarang: perbaiki tipenya', test: (line) => /@ts-(ignore|nocheck)/.test(line), skip: () => false, inComments: true },
    {
        name: 'fetch langsung dilarang: akses server lewat Inertia (router, useForm, Link, Form)',
        test: (line) => /\bfetch\(/.test(line),
        skip: (file) => file.endsWith('hooks/use-network-status.ts'),
    },
    { name: 'dangerouslySetInnerHTML dilarang: tampilkan teks biasa', test: (line) => /dangerouslySetInnerHTML/.test(line), skip: () => false },
    {
        name: 'localStorage/sessionStorage dilarang: rahasia tidak boleh disimpan di peramban',
        test: (line) => /\b(localStorage|sessionStorage)\b/.test(line),
        // lib/theme.ts keeps the light/dark choice, a preference and not a secret.
        skip: (file) => file.endsWith('lib/theme.ts') || isTest(file),
    },
    {
        name: 'Ekspor bawaan hanya untuk halaman dan layout',
        test: (line) => /^export default\b/.test(line),
        skip: (file) => /\/(pages|layouts)\//.test(file),
    },
    {
        name: 'URL ditulis sebagai string: pakai fungsi Wayfinder dari @/routes atau @/actions',
        test: (line) => /\b(href|action|to|url)(=|:\s*)["'`]\/(?!\/)/.test(line) && !/^\s*(\/\/|\*|\/\*)/.test(line),
        skip: (file) => isTest(file),
    },
    {
        name: 'key memakai indeks dilarang: pakai id yang stabil',
        test: (line) => /key=\{(i|idx|index)\}/.test(line),
        skip: (file) => file.endsWith('components/ui/data-table.tsx'),
    },
];

let failures = 0;

for (const file of files) {
    const lines = sources.get(file).split('\n');

    lines.forEach((line, index) => {
        const comment = /^\s*(\/\/|\*|\/\*)/.test(line);

        for (const rule of rules) {
            if (comment && !rule.inComments) {
                continue;
            }

            if (!rule.skip(file) && rule.test(line)) {
                failures++;
                console.error(`${relative(process.cwd(), file)}:${index + 1}  ${rule.name}`);
            }
        }
    });
}

// A source file is at most 400 lines (production-standards.md): split a long one by part instead.
for (const file of files) {
    const count = sources.get(file).split('\n').length;

    if (count > 400) {
        failures++;
        console.error(`${relative(process.cwd(), file)}  ${count} baris: batas berkas 400 baris, pecah per bagian`);
    }
}

// A top-level function is at most 80 lines (production-standards.md). Template components adopted as they are: the first two by D-0016,
// the last three by D-0038.
const longFunctionAllowed = [
    'components/ui/data-table.tsx',
    'components/ui/file-input.tsx',
    'components/ui/date-range-picker.tsx',
    'components/ui/file-uploader.tsx',
    'components/ui/tag-input.tsx',
];

for (const file of files) {
    if (longFunctionAllowed.some((allowed) => file.endsWith(allowed))) {
        continue;
    }

    const lines = sources.get(file).split('\n');

    lines.forEach((line, start) => {
        const name = /^(?:export )?(?:default )?(?:async )?function (\w+)/.exec(line)?.[1];
        const end = name === undefined ? -1 : lines.indexOf('}', start);

        if (end - start + 1 > 80) {
            failures++;
            console.error(`${relative(process.cwd(), file)}:${start + 1}  ${name}: ${end - start + 1} baris, batas fungsi 80 baris, pecah per bagian`);
        }
    });
}

// Multi-line rule: buttons inside DialogFooter/CardFooter are text only (see .ai/rules/frontend-react.md, "Tombol").
for (const file of files) {
    const text = sources.get(file);

    for (const match of text.matchAll(/<(DialogFooter|CardFooter)\b[^>]*>([\s\S]*?)<\/\1>/g)) {
        if (/<[A-Z][A-Za-z0-9]*\s*\/>/.test(match[2] ?? '')) {
            failures++;
            console.error(
                `${relative(process.cwd(), file)}:${text.slice(0, match.index).split('\n').length}  Tombol di footer dialog/kartu hanya teks, tanpa ikon`,
            );
        }
    }
}

// Multi-line rule: a screen that opens a Modal supplies the dialog body with DialogBody (header, body, footer; see `.ai/rules/frontend-react.md`).
for (const file of files) {
    const text = sources.get(file);

    if (/<Modal\b/.test(text) && !/\bDialogBody\b/.test(text) && !file.endsWith('components/ui/dialog.tsx') && !isTest(file)) {
        failures++;
        console.error(`${relative(process.cwd(), file)}  Dialog tanpa DialogBody: isi dialog dibungkus DialogBody (header, body, footer)`);
    }
}

// Multi-line rule: PageHeader gives its action buttons equal widths by itself, so `actions` is the buttons (a fragment), never a wrapping div.
for (const file of files) {
    const text = sources.get(file);

    if (/<PageHeader\b[^>]*?actions=\{\s*<div\b/s.test(text) && !isTest(file)) {
        failures++;
        console.error(
            `${relative(process.cwd(), file)}  PageHeader: actions berisi tombolnya langsung (fragment), tanpa div pembungkus, supaya tombolnya sama lebar`,
        );
    }
}

// Multi-line rule: comments stay short. More than 8 lines in a row is documentation, and documentation lives in docs/.
for (const file of files) {
    let run = 0;
    let start = 0;

    [...sources.get(file).split('\n'), ''].forEach((line, index) => {
        if (/^\s*(\/\/|\/\*|\*)/.test(line)) {
            run++;
            start = run === 1 ? index + 1 : start;
        } else {
            if (run > 8 && !isTest(file)) {
                failures++;
                console.error(`${relative(process.cwd(), file)}:${start}  Komentar ${run} baris berturut-turut: ringkas (maksimal 8) atau pindahkan ke docs/`);
            }

            run = 0;
        }
    });
}

// Multi-line rule: a file that draws a calendar must import its stylesheet itself; without it the calendar shows unstyled (a lazy page may
// load without any other calendar having brought the CSS along).
for (const file of files) {
    const text = sources.get(file);

    if (/from 'react-day-picker'/.test(text) && !/import 'react-day-picker\/style\.css'/.test(text) && !isTest(file)) {
        failures++;
        console.error(`${relative(process.cwd(), file)}  Kalender tanpa import 'react-day-picker/style.css' di berkas yang sama`);
    }
}

if (failures > 0) {
    console.error(`\n${failures} pelanggaran standar kode.`);
    process.exit(1);
}

console.log(`Standar kode terpenuhi (${files.length} berkas diperiksa).`);
