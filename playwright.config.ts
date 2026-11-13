/// <reference types="node" />
import { defineConfig, devices } from '@playwright/test';
import path from 'node:path';

// End-to-end tests drive the real app (Laravel and the built assets) against their own sqlite file, never the working database.
// Run `npm run build` once, then `npm run test:e2e`. The server migrates and seeds the demo accounts (`superadmin`, password `password`).
const port = 8123;
const baseURL = `http://127.0.0.1:${port}`;
const database = path.resolve('database/e2e.sqlite');

export default defineConfig({
    testDir: 'e2e',
    testMatch: '*.e2e.ts',
    // One database and one session store: the scenarios run one after another.
    fullyParallel: false,
    workers: 1,
    retries: process.env.CI ? 1 : 0,
    use: { baseURL, trace: 'retain-on-failure' },
    // Chromium by default; `--project=firefox` or `--project=webkit` (and CI) cover the other engines.
    projects: [
        { name: 'chromium', use: { ...devices['Desktop Chrome'] } },
        { name: 'firefox', use: { ...devices['Desktop Firefox'] } },
        { name: 'webkit', use: { ...devices['Desktop Safari'] } },
    ],
    webServer: {
        command: `php -r "touch('${database}');" && php artisan migrate:fresh --force && php artisan db:seed --force && php artisan serve --host=127.0.0.1 --port=${port}`,
        url: baseURL,
        reuseExistingServer: !process.env.CI,
        timeout: 120_000,
        env: {
            APP_ENV: 'testing',
            APP_URL: baseURL,
            DB_CONNECTION: 'sqlite',
            DB_DATABASE: database,
            SEED_DEMO_ACCOUNTS: 'true',
            SESSION_DRIVER: 'file',
            CACHE_STORE: 'file',
            QUEUE_CONNECTION: 'sync',
        },
    },
});
