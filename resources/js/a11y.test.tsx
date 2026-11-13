import { render } from '@testing-library/react';
import { axe } from 'vitest-axe';
import { beforeAll, describe, expect, it, vi } from 'vitest';
import { ErrorPage } from '@/components/error-page';
import { ReauthProvider } from '@/components/reauth/reauth-provider';
import { TooltipProvider } from '@/components/ui/tooltip';
import StorageFiles from '@/pages/storage-files';
import Login from '@/pages/login';
import UiGuideline from '@/pages/ui-guideline';

// The pages read the shared props and the current address, and set the document title; the test gives them a signed-out visitor on the home page.
vi.mock('@inertiajs/react', async (importOriginal) => ({
    ...(await importOriginal<Record<string, unknown>>()),
    Head: () => null,
    usePage: () => ({ props: { flash: { message: null }, auth: { user: { confirmed_until: 0 } } }, url: '/' }),
}));

// jsdom has no layout or colour, so these checks cover structure and names (labels, roles, ids); contrast is held by index.contrast.test.ts.
const scan = async (container: HTMLElement) => {
    // axe cannot look inside iframes in jsdom (the UI Guideline previews the static error pages and the email in some).
    container.querySelectorAll('iframe').forEach((frame) => frame.remove());
    const results = await axe(container, { rules: { 'color-contrast': { enabled: false }, region: { enabled: false } } });

    return results.violations.map((v) => {
        const found = v.nodes.map((n) => container.querySelector(String(n.target[0]))?.outerHTML.slice(0, 600) ?? n.target.join(' '));

        return `${v.id}: ${found.join(' | ')}`;
    });
};

const inShell = (page: React.ReactNode) => <TooltipProvider>{page}</TooltipProvider>;

describe('accessibility', () => {
    beforeAll(() => {
        // Radix measures some controls; jsdom has no ResizeObserver.
        window.ResizeObserver = class {
            observe() {}
            unobserve() {}
            disconnect() {}
        };
    });

    it('sign-in page has no violations', async () => {
        const { container } = render(inShell(<Login />));

        expect(await scan(container)).toEqual([]);
    });

    it('404 page has no violations', async () => {
        const { container } = render(inShell(<ErrorPage status={404} />));

        expect(await scan(container)).toEqual([]);
    });

    it('the storage file manager has no violations', async () => {
        const files = {
            data: [
                { name: 'Laporan', path: 'Laporan', type: 'folder' as const, size: null, modified: null },
                { name: 'ringkasan.pdf', path: 'ringkasan.pdf', type: 'file' as const, size: 2048, modified: '2026-11-13T10:00:00+07:00' },
            ],
            meta: { current_page: 1, last_page: 1, from: 1, to: 2, total: 2 },
        };
        const filters = { search: null, sort: 'name', direction: 'asc' as const, per_page: 50 };
        const { container } = render(
            inShell(
                <ReauthProvider>
                    <StorageFiles
                        storage={{ type: 's3', label: 'Amazon S3' }}
                        path=""
                        files={files}
                        filters={filters}
                        truncated={false}
                        error={null}
                        limits={{ max_mb: 10, extensions: ['pdf'], preview: { png: 'image', pdf: 'pdf', txt: 'text' }, preview_max_mb: 5 }}
                    />
                </ReauthProvider>,
            ),
        );

        expect(await scan(container)).toEqual([]);
    });

    it('UI Guideline has no violations', async () => {
        const { container } = render(inShell(<UiGuideline />));

        expect(await scan(container)).toEqual([]);
    }, 30_000);
});
