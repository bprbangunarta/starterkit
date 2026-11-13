import '@testing-library/jest-dom/vitest';
import type * as Inertia from '@inertiajs/react';
import { cleanup } from '@testing-library/react';
import { afterEach, vi } from 'vitest';
import { APP_NAME } from '@/lib/brand';

// `Head` needs a running Inertia app. Component tests have none, so it is replaced by what it does for the tab: set the title.
vi.mock('@inertiajs/react', async (importOriginal) => ({
    ...(await importOriginal<typeof Inertia>()),
    Head: ({ title }: { title?: string }) => {
        if (title !== undefined) {
            document.title = `${title} - ${APP_NAME}`;
        }

        return null;
    },
}));

// jsdom has no matchMedia; this one says "no match" (light, wide-screen rules off). Tests that need another answer replace it.
if (typeof window !== 'undefined' && typeof window.matchMedia !== 'function') {
    window.matchMedia = (query: string) =>
        ({ matches: false, media: query, addEventListener: () => undefined, removeEventListener: () => undefined }) as unknown as MediaQueryList;
}

// Tests that run in the node environment (file reads) have no DOM to clean.
afterEach(() => {
    if (typeof document !== 'undefined') {
        cleanup();
        localStorage.clear();
    }
});
