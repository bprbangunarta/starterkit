import path from 'node:path';
import react from '@vitejs/plugin-react';
import { defineConfig } from 'vitest/config';

// Separate from vite.config.js so tests do not start the Laravel, Inertia, and Wayfinder plugins.
export default defineConfig({
    plugins: [react()],
    resolve: { alias: { '@': path.resolve(import.meta.dirname, 'resources/js') } },
    test: { environment: 'jsdom', setupFiles: ['./resources/js/test/setup.ts'], include: ['resources/js/**/*.test.{ts,tsx}'], css: false },
});
