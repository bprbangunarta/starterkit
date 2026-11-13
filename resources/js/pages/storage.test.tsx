import type * as Inertia from '@inertiajs/react';
import * as React from 'react';
import { render, screen, within } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { beforeAll, beforeEach, describe, expect, it, vi } from 'vitest';
import type { StorageEntry } from '@/components/storage/types';
import { TooltipProvider } from '@/components/ui/tooltip';
import Storage from '@/pages/storage';

const inertia = vi.hoisted(() => ({ post: vi.fn(), delete: vi.fn(), patch: vi.fn() }));

vi.mock('@inertiajs/react', async (importOriginal) => {
    const original = await importOriginal<typeof Inertia>();

    // The form hook talks to the page's own router, so the hook is replaced by one that records the request.
    const useForm = (initial: Record<string, unknown>) => {
        const [data, setData] = React.useState(initial);

        return {
            data,
            setData: (key: string, value: unknown): void => setData((current) => ({ ...current, [key]: value })),
            errors: {},
            processing: false,
            patch: (url: string, options: unknown): void => {
                inertia.patch(url, data, options);
            },
        };
    };

    return { ...original, Head: () => null, useForm, router: { ...original.router, post: inertia.post, delete: inertia.delete } };
});

// The password confirmation has its own tests; here the action just runs.
vi.mock('@/components/reauth/reauth-provider', () => ({ useReauth: () => ({ confirmed: (action: () => void) => action() }) }));

const s3 = (overrides: Partial<StorageEntry> = {}): StorageEntry =>
    ({
        type: 's3',
        label: 'Amazon S3',
        kind: 'Amazon S3',
        editable: true,
        active: false,
        name: null,
        source: 'env',
        configured: true,
        summary: 'bucket-contoh · ap-southeast-3',
        form: { region: 'ap-southeast-3', bucket: 'bucket-contoh', endpoint: '', use_path_style: false, root: '' },
        secrets: { key_hint: '••••0001', secret_set: true },
        tested_at: null,
        test_ok: null,
        test_message: null,
        ...overrides,
    }) as StorageEntry;

const sftp = (overrides: Partial<StorageEntry> = {}): StorageEntry =>
    ({
        type: 'sftp',
        label: 'SFTP',
        kind: 'SFTP',
        editable: true,
        active: false,
        name: null,
        source: 'env',
        configured: false,
        summary: null,
        form: { host: '', port: '22', username: '', root: '' },
        secrets: { password_set: false, private_key_set: false },
        tested_at: null,
        test_ok: null,
        test_message: null,
        ...overrides,
    }) as StorageEntry;

/** Opens the actions menu of a storage row and returns it. */
async function openActions(user: ReturnType<typeof userEvent.setup>, storage: string) {
    await user.click(screen.getByRole('button', { name: `Aksi ${storage}` }));

    return screen.getByRole('menu');
}

const local = (overrides: Partial<StorageEntry> = {}): StorageEntry =>
    ({
        type: 'local',
        label: 'Lokal',
        kind: 'Lokal',
        editable: false,
        active: true,
        name: null,
        source: 'env',
        configured: true,
        summary: 'storage/app/private',
        form: {},
        secrets: {},
        tested_at: null,
        test_ok: null,
        test_message: null,
        ...overrides,
    }) as StorageEntry;

const renderPage = (storages: StorageEntry[]) =>
    render(
        <TooltipProvider>
            <Storage storages={storages} />
        </TooltipProvider>,
    );

describe('Storage page', () => {
    beforeAll(() => {
        // The switch of the S3 form measures itself; jsdom has no ResizeObserver.
        window.ResizeObserver = class {
            observe() {}
            unobserve() {}
            disconnect() {}
        };
    });

    beforeEach(() => {
        inertia.post.mockReset();
        inertia.delete.mockReset();
    });

    it('lists both kinds with where the connection comes from and what it points to', () => {
        renderPage([s3({ source: 'screen' }), sftp()]);

        const rows = screen.getAllByRole('row');
        expect(within(rows[1]).getByText('Amazon S3')).toBeInTheDocument();
        expect(within(rows[1]).getByText('Layar')).toBeInTheDocument();
        expect(within(rows[1]).getByText('bucket-contoh · ap-southeast-3')).toBeInTheDocument();
        expect(within(rows[2]).getByText('.env')).toBeInTheDocument();
        expect(within(rows[2]).getByText('Belum lengkap')).toBeInTheDocument();
    });

    it('tests only a storage that is configured', async () => {
        const user = userEvent.setup();
        renderPage([s3(), sftp()]);

        expect(within(await openActions(user, 'Amazon S3')).getByRole('menuitem', { name: 'Koneksi' })).not.toHaveAttribute('aria-disabled');
        await user.keyboard('{Escape}');
        expect(within(await openActions(user, 'SFTP')).getByRole('menuitem', { name: 'Koneksi' })).toHaveAttribute('aria-disabled', 'true');
    });

    it('offers to go back to the environment only for what was saved on the screen', async () => {
        const user = userEvent.setup();
        renderPage([s3({ source: 'screen' }), sftp()]);

        expect(within(await openActions(user, 'Amazon S3')).getByRole('menuitem', { name: 'Restore' })).toBeInTheDocument();
        await user.keyboard('{Escape}');
        expect(within(await openActions(user, 'SFTP')).queryByRole('menuitem', { name: 'Restore' })).not.toBeInTheDocument();
    });

    it('opens the files of a storage that is configured, and only of that one', () => {
        renderPage([s3(), sftp()]);

        expect(screen.getByRole('link', { name: 'Buka berkas Amazon S3' })).toHaveAttribute('href', '/storage/s3/files');
        expect(screen.getByRole('button', { name: 'Buka berkas SFTP' })).toBeDisabled();
    });

    it('shows the last test with its result', () => {
        renderPage([s3({ tested_at: '2026-11-13T10:00:00+07:00', test_ok: false, test_message: 'Gagal terhubung: waktu habis' }), sftp()]);

        expect(screen.getByText('Gagal')).toBeInTheDocument();
    });

    it('sends the test to the server for that storage', async () => {
        const user = userEvent.setup();
        renderPage([s3(), sftp()]);

        await user.click(within(await openActions(user, 'Amazon S3')).getByRole('menuitem', { name: 'Koneksi' }));

        expect(inertia.post).toHaveBeenCalledWith('/storage/s3/test', {}, expect.objectContaining({ preserveScroll: true }));
    });

    it('opens the form of S3 with the saved values, never a secret', async () => {
        const user = userEvent.setup();
        renderPage([s3(), sftp()]);

        await user.click(within(await openActions(user, 'Amazon S3')).getByRole('menuitem', { name: 'Konfig' }));

        const dialog = screen.getByRole('dialog', { name: 'Ubah Konfigurasi Amazon S3' });
        expect(within(dialog).getByLabelText(/Bucket/)).toHaveValue('bucket-contoh');
        expect(within(dialog).getByLabelText(/Secret key/)).toHaveValue('');
        expect(within(dialog).getByLabelText(/Access key/)).toHaveAttribute('placeholder', '••••0001');
    });

    it('shows the given name with its kind, and saves a new name', async () => {
        const user = userEvent.setup();
        renderPage([s3({ label: 'Arsip Kantor', name: 'Arsip Kantor' }), sftp()]);

        expect(screen.getByText('Arsip Kantor')).toBeInTheDocument();
        await user.click(within(await openActions(user, 'Arsip Kantor')).getByRole('menuitem', { name: 'Rename' }));
        const dialog = screen.getByRole('dialog', { name: 'Ubah Nama Penyimpanan' });
        await user.clear(within(dialog).getByLabelText(/Nama/));
        await user.type(within(dialog).getByLabelText(/Nama/), 'Arsip Pusat');
        await user.click(within(dialog).getByRole('button', { name: 'Simpan' }));

        expect(inertia.patch).toHaveBeenCalledWith('/storage/s3/name', { name: 'Arsip Pusat' }, expect.objectContaining({ preserveScroll: true }));
    });

    it('lets the local storage be named and tested, but not configured or reset', async () => {
        const user = userEvent.setup();
        renderPage([local({ source: 'screen' }), s3()]);

        expect(screen.getByText('Proyek')).toBeInTheDocument();
        const menu = within(await openActions(user, 'Lokal'));
        expect(menu.getAllByRole('menuitem').map((item) => item.textContent)).toEqual(['Rename', 'Koneksi']);
    });

    it('asks before going back to the environment, then deletes the saved connection', async () => {
        const user = userEvent.setup();
        renderPage([s3({ source: 'screen' }), sftp()]);

        await user.click(within(await openActions(user, 'Amazon S3')).getByRole('menuitem', { name: 'Restore' }));
        expect(inertia.delete).not.toHaveBeenCalled();
        await user.click(within(screen.getByRole('alertdialog')).getByRole('button', { name: 'Kembalikan' }));

        expect(inertia.delete).toHaveBeenCalledWith('/storage/s3', expect.objectContaining({ preserveScroll: true }));
    });
});
