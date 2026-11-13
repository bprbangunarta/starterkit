import * as React from 'react';
import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { beforeAll, describe, expect, it, vi } from 'vitest';
import type * as Inertia from '@inertiajs/react';
import { TooltipProvider } from '@/components/ui/tooltip';
import Settings from '@/pages/settings';

const inertia = vi.hoisted(() => ({ patch: vi.fn() }));

vi.mock('@inertiajs/react', async (importOriginal) => {
    const original = await importOriginal<typeof Inertia>();
    // The form hook talks to the page's own router, so the hook is replaced by one that records the request.
    const useForm = (initial: Record<string, string>) => {
        const [data, setData] = React.useState(initial);

        return {
            data,
            setData: (key: string, value: string): void => setData((current) => ({ ...current, [key]: value })),
            errors: { active_storage: 'Penyimpanan ini belum bisa dipakai.' } as Record<string, string>,
            processing: false,
            patch: (url: string, options: unknown): void => {
                inertia.patch(url, data, options);
            },
        };
    };

    return { ...original, Head: () => null, useForm };
});

// The password confirmation has its own tests; here the action just runs.
vi.mock('@/components/reauth/reauth-provider', () => ({ useReauth: () => ({ confirmed: (action: () => void) => action() }) }));

const images = {
    logo: { url: '/logo.png', custom: false, mimes: ['png', 'jpg'], max_kb: 512 },
    favicon: { url: '/brand/favicon?v=abc', custom: true, mimes: ['png', 'ico'], max_kb: 128 },
};
const settings = { app_name: 'STARTERKIT', meta_description: '', active_storage: 'local' };
const storages = [
    { value: 'local', label: 'Lokal' },
    { value: 's3', label: 'Arsip Kantor' },
];

describe('Settings page', () => {
    beforeAll(() => {
        // Radix measures some controls; jsdom has no ResizeObserver.
        window.ResizeObserver = class {
            observe() {}
            unobserve() {}
            disconnect() {}
        };
    });

    it('shows the saved values and offers the storages that can be used, the active one chosen', () => {
        render(
            <TooltipProvider>
                <Settings settings={settings} images={images} storages={storages} />
            </TooltipProvider>,
        );

        expect(screen.getByLabelText(/Nama aplikasi/)).toHaveValue('STARTERKIT');
        expect(screen.getByRole('radio', { name: 'Lokal' })).toBeChecked();
        expect(screen.getByRole('radio', { name: 'Arsip Kantor' })).not.toBeChecked();
        expect(screen.getByText('Penyimpanan ini belum bisa dipakai.')).toBeInTheDocument();
    });

    it('saves what was typed and chosen, after the password is confirmed', async () => {
        const user = userEvent.setup();
        render(
            <TooltipProvider>
                <Settings settings={settings} images={images} storages={storages} />
            </TooltipProvider>,
        );

        await user.clear(screen.getByLabelText(/Nama aplikasi/));
        await user.type(screen.getByLabelText(/Nama aplikasi/), 'STARTERKIT Dua');
        await user.click(screen.getByRole('radio', { name: 'Arsip Kantor' }));
        await user.click(screen.getByRole('button', { name: 'Simpan' }));

        expect(inertia.patch).toHaveBeenCalledWith(
            '/settings',
            { app_name: 'STARTERKIT Dua', meta_description: '', active_storage: 's3' },
            expect.objectContaining({ preserveScroll: true }),
        );
    });

    it('shows each image, and offers to go back to the bundled one only for what was uploaded', () => {
        render(
            <TooltipProvider>
                <Settings settings={settings} images={images} storages={storages} />
            </TooltipProvider>,
        );

        expect(screen.getByRole('img', { name: 'Logo saat ini' })).toHaveAttribute('src', '/logo.png');
        expect(screen.getByRole('img', { name: 'Favicon saat ini' })).toHaveAttribute('src', '/brand/favicon?v=abc');
        expect(screen.getAllByRole('button', { name: 'Kembalikan' })).toHaveLength(1);
        expect(screen.getAllByRole('button', { name: 'Unggah' })).toHaveLength(2);
        expect(screen.getAllByRole('button', { name: 'Unggah' })[0]).toBeDisabled();
    });
});
