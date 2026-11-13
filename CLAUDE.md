<laravel-boost-guidelines>
=== .ai/production-standards rules ===

# Standar produksi

## Produksi, bukan prototipe

- Tidak ada mock, stub, data palsu, atau placeholder diam-diam di kode aplikasi. Bagian yang belum selesai ditandai `TODO: <alasan>` dan disebutkan di laporan akhir.
- Jangan pernah melewati pengecekan: tanpa `--no-verify`, tanpa menurunkan level atau mematikan aturan lint dan static analysis, tanpa melewati atau menghapus test, tanpa menambah baseline. Perbaiki akar masalahnya.
- Utang teknis, risiko yang diterima, dan pengecualian aturan dicatat di `docs/decisions.md`, tidak pernah dibiarkan implisit.
- Agent tidak boleh menerima risiko atau menyimpang dari rule atas nama pemilik. Penyimpangan dicatat dengan status `diusulkan` dan `Diputuskan oleh: menunggu pemilik`, lalu disebut di laporan akhir agar user memutuskan. Entri berstatus `berlaku` baru ditulis setelah user menyetujuinya di percakapan.
- Jangan mengubah kode lama di luar permintaan demi memenuhi rules atau lolos review. Rules berlaku untuk kode yang ditulis atau diubah. Pelanggaran yang ditemukan di kode lama dilaporkan kepada user di laporan akhir, bukan diperbaiki diam-diam.
- Laporkan dengan jujur. Test yang tidak dijalankan tidak disebut lolos, dan hal yang hanya dibaca tanpa diuji disebut "belum diverifikasi".

## Batas default

Berlaku kecuali proyek menetapkan nilai lain di `docs/decisions.md`:

| Hal | Batas |
| --- | --- |
| Panggilan HTTP keluar | Timeout total 10 detik, connect 3 detik |
| Retry | Maksimal 3 percobaan dengan jeda bertambah, hanya untuk operasi yang aman diulang, dan tidak lebih dari 3 di semua lapisan |
| Ukuran halaman | Maksimal 100 item |
| Body request dan upload | 1 MB untuk body, 10 MB untuk file |
| Rate limit umum | 60 request per menit per user atau IP |
| Ekspor | Maksimal 50.000 baris |
| Konfirmasi password untuk aksi sensitif | Berlaku 15 menit |
| Transaksi database | Selesai di bawah 1 detik |

## Kualitas desain

- Method melakukan satu hal: targetkan 40 baris, maksimal 80, dengan nesting maksimal 4 tingkat. File maksimal 400 baris, kecuali migration, file hasil generate, dan fixture. Batas ini belum ditegakkan alat, jadi diperiksa saat review.
- Nama menyebut apa sesuatu itu atau apa yang dilakukannya. Class atau file yang namanya hanya kata generik (`Helper`, `Utils`, `Common`, `Manager`, `Misc`) tidak diizinkan. Kata generik di dalam nama yang tepat (`ConnectionPoolManager`) boleh.
- Early return lebih baik daripada kondisi bersarang. Parameter boolean yang mengubah perilaku method berarti seharusnya ada dua method. Himpunan nilai tertutup memakai enum. Tidak ada dead code.
- Kasus baru dari keluarga yang sama ditambahkan di satu tempat (kasus enum, baris config, atau registry). Jika kasus ketiga butuh mengedit banyak file, buat registry dulu.
- Komentar menjelaskan mengapa, bukan apa. Ekstrak kode di salinan ketiga, atau di salinan kedua jika keduanya mewakili satu aturan bisnis yang harus berubah bersama.
- Nilai yang bisa diubah operator berasal dari config. Nilai kebijakan (batas, aturan) ditulis sekali, di config atau konstanta bernama.

## Perbaikan bug

- Perbaiki akar masalahnya, bukan gejalanya. Tulis test regresi yang gagal lebih dulu, baru perbaiki.
- Cari defek yang sama dengan bug yang sedang diperbaiki di seluruh repositori. Perbaiki kemunculannya di file yang sudah kamu ubah, dan sebutkan sisanya di laporan.

## Library dan dependensi

- Sebelum memakai API library, pastikan API itu ada di versi yang terpasang (lewat `search-docs` atau source di `vendor/` dan `node_modules/`). Jangan memakai API yang diingat dari versi lain.
- Dependensi baru atau kenaikan versi mayor butuh persetujuan user. Ajukan dengan kebutuhan, alternatif, lisensi, status pemeliharaan, dan dampak keamanan, serta pastikan nama paketnya persis ada di Packagist atau npm.
- Kenaikan versi patch atau minor untuk menutup temuan `composer audit` atau `npm audit` boleh tanpa persetujuan baru, selama test lolos.
- Jangan pernah menjalankan script dari sumber tak dikenal (`curl | sh`).

=== .ai/project-memory rules ===

# Memori proyek (memory/PRD.md)

- `memory/PRD.md` adalah memori bersama proyek untuk semua agent, supaya konteks tidak hilang saat berpindah agent atau sesi. Isinya konteks produk: tujuan aplikasi, pengguna, fitur beserta statusnya, keputusan produk dan alasannya, serta pekerjaan yang sedang berjalan.
- Baca `memory/PRD.md` di awal setiap sesi, sebelum merencanakan atau mengubah kode. Jika file itu belum ada, tawarkan kepada user untuk membuatnya.
- Perbarui `memory/PRD.md` setelah fitur selesai, ruang lingkup berubah, atau ada keputusan produk baru. Tulis singkat, sertakan tanggal (YYYY-MM-DD), dan hapus informasi yang sudah tidak berlaku.
- Pembagian tempat:
  - Standar penulisan kode dicatat di `.ai/rules` lewat tool `record-rule`, bukan di `memory/PRD.md`.
  - Konteks produk dan progres dicatat di `memory/PRD.md`.
  - Hal yang bisa dibaca langsung dari kode atau riwayat git tidak perlu dicatat.
- Jangan menulis secret, kredensial, atau data pribadi ke `memory/PRD.md`.
- Tulis isi `memory/PRD.md` dalam bahasa Indonesia.

=== foundation rules ===

# Laravel Boost Guidelines

## Foundational Context

This application is a Laravel application running on PHP 8.5. Always use the APIs that match the installed major version of each package — do not assume a version.

Before relying on a package's API, confirm its installed version:
- PHP packages: run `composer show --direct` to list direct dependencies with versions, or `composer show <vendor/package>` for a single package.
- JS packages: check `package.json` for the installed versions.

## Skills Activation

This project has domain-specific skills available in `**/skills/**`. You MUST activate the relevant skill whenever you work in that domain—don't wait until you're stuck.

## Conventions

- You must follow all existing code conventions used in this application. When creating or editing a file, check sibling files for the correct structure, approach, and naming.
- Use descriptive names for variables and methods. For example, `isRegisteredForDiscounts`, not `discount()`.
- Check for existing components to reuse before writing a new one.

## Verification Scripts

- Do not create verification scripts or tinker when tests cover that functionality and prove they work. Unit and feature tests are more important.

## Application Structure & Architecture

- Stick to existing directory structure; don't create new base folders without approval.
- Do not change the application's dependencies without approval.

## Frontend Bundling

- If a frontend change doesn't show in the UI or you get a "Unable to locate file in Vite manifest" error, run `npm run build` or ask the user to run `npm run dev` or `composer run dev`.

## Documentation Files

- You must only create documentation files if explicitly requested by the user.

=== boost rules ===

# Laravel Boost

## Tools

- Laravel Boost is an MCP server with tools designed specifically for this application. Prefer Boost tools over manual alternatives like shell commands or file reads.
- Use `database-query` to run read-only queries against the database instead of writing raw SQL in tinker.
- Use `database-schema` to inspect table structure before writing migrations or models.
- Use `get-absolute-url` to resolve the correct scheme, domain, and port for project URLs. Always use this before sharing a URL with the user.
- Use `browser-logs` to read browser logs, errors, and exceptions. Only recent logs are useful, ignore old entries.

## Searching Documentation (IMPORTANT)

- Use `search-docs` before changes that depend on Laravel ecosystem APIs, behavior, configuration, or version-specific syntax. Skip it for copy-only edits and other changes where package documentation is irrelevant. Reuse sufficient results already in context instead of searching again.
- Pass a `packages` array to scope results when you know which packages are relevant.
- Use multiple broad, topic-based queries: `['rate limiting', 'routing rate limiting', 'routing']`. Expect the most relevant results first.
- Do not add package names to queries because package info is already shared. Use `test resource table`, not `filament 4 test resource table`.

### Search Syntax

1. Use words for auto-stemmed AND logic: `rate limit` matches both "rate" AND "limit".
2. Use `"quoted phrases"` for exact position matching: `"infinite scroll"` requires adjacent words in order.
3. Combine words and phrases for mixed queries: `middleware "rate limit"`.
4. Use multiple queries for OR logic: `queries=["authentication", "middleware"]`.

## Project Rules

- This project contains committed, area-grouped rules in `.ai/rules` when that directory exists, including path-scoped framework guidelines under `.ai/rules/boost`. Before you enter plan mode or create/edit any file, you MUST first: open @.ai/rules/index.md (it maps file globs to rule files), read every rule file whose globs cover the path(s) in scope, and run `grep -rin 'keyword' .ai/rules` to catch what a path match alone misses. Do not write code until you have read and are following every matching rule. If `.ai/rules` does not exist, continue without it.

## Artisan

- Run Artisan commands directly via the command line (e.g., `php artisan route:list`). Use `php artisan list` to discover available commands and `php artisan [command] --help` to check parameters.
- Inspect routes with `php artisan route:list`. Filter with: `--method=GET`, `--name=users`, `--path=api`, `--except-vendor`, `--only-vendor`.
- Read configuration values using dot notation: `php artisan config:show app.name`, `php artisan config:show database.default`. Or read config files directly from the `config/` directory.

## Tinker

- Execute PHP in app context for debugging and testing code. Do not create models without user approval, prefer tests with factories instead. Prefer existing Artisan commands over custom tinker code.
- Always use single quotes to prevent shell expansion: `php artisan tinker --execute 'Your::code();'`
  - Double quotes for PHP strings inside: `php artisan tinker --execute 'User::where("active", true)->count();'`

=== php rules ===

# PHP

- Always use curly braces for control structures, even for single-line bodies.
- Use PHP 8 constructor property promotion: `public function __construct(public GitHub $github) { }`. Do not leave empty zero-parameter `__construct()` methods unless the constructor is private.
- Use explicit return type declarations and type hints for all method parameters: `function isAccessible(User $user, ?string $path = null): bool`
- Use TitleCase for Enum keys: `FavoritePerson`, `BestLake`, `Monthly`.
- Prefer PHPDoc blocks over inline comments. Only add inline comments for exceptionally complex logic.
- Use array shape type definitions in PHPDoc blocks.

=== deployments rules ===

# Deployment

- Laravel can be deployed using [Laravel Cloud](https://cloud.laravel.com/), which is the fastest way to deploy and scale production Laravel applications.

=== tests rules ===

# Test Enforcement

- Add or update tests for behavior and logic changes when a test provides meaningful regression coverage.
- Pure copy, styling, and layout-only changes do not require new or updated tests.
- When test coverage applies, run the affected tests and ensure they pass.
- Test the changed behavior and its important failure modes, but do not add tests beyond them.
- Read the `testing-best-practices` skill before writing tests.

=== inertia-laravel/core rules ===

# Inertia

- Inertia creates fully client-side rendered SPAs without modern SPA complexity, leveraging existing server-side patterns.
- Components live in `resources/js/pages` (unless specified in `vite.config.js`). Use `Inertia::render()` for server-side routing instead of Blade views.
- ALWAYS use `search-docs` tool for version-specific Inertia documentation and updated code examples.
- IMPORTANT: Activate `inertia-react-development` when working with Inertia client-side patterns.

# Inertia v3

- Use all Inertia features from v1, v2, and v3. Check the documentation before making changes to ensure the correct approach.
- New v3 features: standalone HTTP requests (`useHttp` hook), optimistic updates with automatic rollback, layout props (`useLayoutProps` hook), instant visits, simplified SSR via `@inertiajs/vite` plugin, custom exception handling for error pages.
- Carried over from v2: deferred props, infinite scroll, merging props, polling, prefetching, once props, flash data.
- When using deferred props, add an empty state with a pulsing or animated skeleton.
- Axios has been removed. Use the built-in XHR client with interceptors, or install Axios separately if needed.
- `Inertia::lazy()` / `LazyProp` has been removed. Use `Inertia::optional()` instead.
- Prop types (`Inertia::optional()`, `Inertia::defer()`, `Inertia::merge()`) work inside nested arrays with dot-notation paths.
- SSR works automatically in Vite dev mode with `@inertiajs/vite` - no separate Node.js server needed during development.
- Event renames: `invalid` is now `httpException`, `exception` is now `networkError`.
- `router.cancel()` replaced by `router.cancelAll()`.
- The `future` configuration namespace has been removed - all v2 future options are now always enabled.

=== laravel/core rules ===

# Do Things the Laravel Way

- Use `php artisan make:` commands to create new files (i.e. migrations, controllers, models, etc.). You can list available Artisan commands using `php artisan list` and check their parameters with `php artisan [command] --help`.
- If you're creating a generic PHP class, use `php artisan make:class`.
- Pass `--no-interaction` to all Artisan commands to ensure they work without user input. You should also pass the correct `--options` to ensure correct behavior.

### Model Creation

- When creating new models, create useful factories and seeders for them too. Ask the user if they need any other things, using `php artisan make:model --help` to check the available options.

## APIs & Eloquent Resources

- For APIs, default to using Eloquent API Resources and API versioning unless existing API routes do not, then you should follow existing application convention.

## URL Generation

- When generating links to other pages, prefer named routes and the `route()` function.

## Testing

- When creating models for tests, use the factories for the models. Check if the factory has custom states that can be used before manually setting up the model.
- Faker: Use methods such as `$this->faker->word()` or `fake()->randomDigit()`. Follow existing conventions whether to use `$this->faker` or `fake()`.
- When creating tests, make use of `php artisan make:test [options] {name}` to create a feature test, and pass `--unit` to create a unit test. Most tests should be feature tests.

=== wayfinder/core rules ===

# Laravel Wayfinder

Use Wayfinder to generate TypeScript functions for Laravel routes. Import from `@/actions/` (controllers) or `@/routes/` (named routes).

=== pint/core rules ===

# Laravel Pint Code Formatter

- If you have modified any PHP files, you must run `vendor/bin/pint --dirty --format agent` before finalizing changes to ensure your code matches the project's expected style.
- Do not run `vendor/bin/pint --test --format agent`, simply run `vendor/bin/pint --format agent` to fix any formatting issues.

=== pest/core rules ===

# Pest

- This project uses Pest. Create tests with `php artisan make:test --pest {name}`.
- Do not include the test suite directory in `{name}`. Use `SomeFeatureTest`, not `Feature/SomeFeatureTest`.
- Read the `testing-best-practices` skill for guidance on coverage, naming, structure, dependency isolation, and review.
- Do not delete tests or test files without approval. They are part of the application.

## Running Tests

- Run the narrowest set of tests that covers the change. Pass a file path or `--filter=testName` to `php artisan test --compact`.
- Rerun a test after each change to it.
- Run `vendor/bin/pest` to call the test runner directly. It accepts the same file path and `--filter=testName` arguments.

=== inertia-react/core rules ===

# Inertia + React

- IMPORTANT: Activate `inertia-react-development` when working with Inertia React client-side patterns.

=== spatie/laravel-activitylog/core rules ===

## Activitylog

- `spatie/laravel-activitylog` logs model events and manual activities to a database table, with subjects, causers, tracked attribute changes, and custom properties.
- Always activate the `activitylog-development` skill when working with activity logging or any code that uses the `LogsActivity`, `CausesActivity`, or `HasActivity` traits, `LogOptions`, the `Activity` model, or the `activity()` helper.
- If the skill is unavailable, read `vendor/spatie/laravel-activitylog/resources/boost/skills/activitylog-development/SKILL.md` before making changes.

</laravel-boost-guidelines>
