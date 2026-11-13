import { render, screen, within } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { beforeAll, beforeEach, describe, expect, it, vi } from 'vitest';
import type { FilesPage } from '@/components/storage-files/types';
import { TooltipProvider } from '@/components/ui/tooltip';
import StorageFiles from '@/pages/storage-files';

const inertia = vi.hoisted(() => ({ get: vi.fn(), delete: vi.fn() }));

vi.mock('@inertiajs/react', async (importOriginal) => ({
    ...(await importOriginal<Record<string, unknown>>()),
    Head: () => null,
    router: { get: inertia.get, delete: inertia.delete },
}));

// The password confirmation has its own tests; here the action just runs.
vi.mock('@/components/reauth/reauth-provider', () => ({ useReauth: () => ({ confirmed: (action: () => void) => action() }) }));

const files: FilesPage = {
    data: [
        { name: 'Laporan', path: 'Laporan', type: 'folder', size: null, modified: null },
        { name: 'ringkasan.pdf', path: 'ringkasan.pdf', type: 'file', size: 2048, modified: '2026-11-13T10:00:00+07:00' },
    ],
    meta: { current_page: 1, last_page: 1, from: 1, to: 2, total: 2 },
};

const filters = { search: null, sort: 'name', direction: 'asc' as const, per_page: 25 };

function show(overrides: Partial<React.ComponentProps<typeof StorageFiles>> = {}) {
    render(
        <TooltipProvider>
            <StorageFiles
                storage={{ type: 's3', label: 'Amazon S3' }}
                path=""
                files={files}
                filters={filters}
                truncated={false}
                error={null}
                limits={{ max_mb: 10, extensions: ['pdf'], preview: { png: 'image', pdf: 'pdf', txt: 'text' }, preview_max_mb: 5 }}
                {...overrides}
            />
        </TooltipProvider>,
    );
}

describe('Storage files page', () => {
    beforeAll(() => {
        window.ResizeObserver = class {
            observe() {}
            unobserve() {}
            disconnect() {}
        };
    });

    beforeEach(() => {
        inertia.get.mockReset();
        inertia.delete.mockReset();
    });

    it('lists the folders and files with their kind and size', async () => {
        const user = userEvent.setup();
        show();
        await user.click(screen.getByRole('button', { name: 'Daftar' }));

        const rows = screen.getAllByRole('row');
        expect(within(rows[1]).getByText('Laporan')).toBeInTheDocument();
        expect(within(rows[1]).getByText('Folder')).toBeInTheDocument();
        expect(within(rows[2]).getByText('ringkasan.pdf')).toBeInTheDocument();
        expect(within(rows[2]).getByText('PDF')).toBeInTheDocument();
        expect(within(rows[2]).getByText('2 KB')).toBeInTheDocument();
    });

    it('shows the grid first, with a card for each folder and file, and switches to the table and back', async () => {
        const user = userEvent.setup();
        show();

        expect(screen.queryByRole('table')).not.toBeInTheDocument();
        expect(screen.getByRole('button', { name: 'Kotak' })).toHaveAttribute('aria-pressed', 'true');
        expect(screen.getByRole('button', { name: /^Laporan\s*Folder/ })).toBeInTheDocument();
        expect(screen.getByRole('button', { name: /^ringkasan\.pdf\s*PDF · 2 KB/ })).toBeInTheDocument();

        await user.click(screen.getByRole('button', { name: 'Daftar' }));
        expect(screen.getByRole('table')).toBeInTheDocument();
        expect(screen.getByRole('button', { name: 'Daftar' })).toHaveAttribute('aria-pressed', 'true');

        await user.click(screen.getByRole('button', { name: 'Kotak' }));
        expect(screen.queryByRole('table')).not.toBeInTheDocument();
    });

    it('leads back to the list of storages and to the top of this one', () => {
        show({ path: 'Laporan/2026' });

        expect(screen.getByRole('link', { name: 'Penyimpanan' })).toHaveAttribute('href', '/storage');
        expect(screen.getByRole('link', { name: 'Amazon S3' })).toHaveAttribute('href', '/storage/s3/files');
        expect(screen.getByRole('link', { name: 'Laporan' })).toHaveAttribute('href', '/storage/s3/files?path=Laporan');
        expect(screen.getByText('2026')).toHaveAttribute('aria-current', 'page');
    });

    it('opens a folder by asking the server for it, and a file in a drawer', async () => {
        const user = userEvent.setup();
        show();

        await user.click(screen.getByText('Laporan'));
        expect(inertia.get).toHaveBeenCalledWith('/storage/s3/files?path=Laporan');

        await user.click(screen.getByText('ringkasan.pdf'));
        const drawer = screen.getByRole('dialog', { name: 'ringkasan.pdf' });
        expect(within(drawer).getByText('/ringkasan.pdf')).toBeInTheDocument();
    });

    describe('preview in the drawer', () => {
        const only = (name: string, size: number): FilesPage => ({
            data: [{ name, path: `Arsip/${name}`, type: 'file', size, modified: null }],
            meta: { current_page: 1, last_page: 1, from: 1, to: 1, total: 1 },
        });

        it('shows a PDF in a frame that reads the file from the server', async () => {
            show();

            await userEvent.setup().click(screen.getByText('ringkasan.pdf'));

            expect(screen.getByTitle('Pratinjau ringkasan.pdf')).toHaveAttribute('src', '/storage/s3/files/preview?path=ringkasan.pdf');
        });

        it('shows an image as a picture with its name', async () => {
            show({ files: only('foto.PNG', 1024) });

            await userEvent.setup().click(screen.getByText('foto.PNG'));

            expect(screen.getByRole('img', { name: 'foto.PNG' })).toHaveAttribute('src', '/storage/s3/files/preview?path=Arsip%2Ffoto.PNG');
        });

        it.each([
            ['a kind the server does not show', 'laporan.docx', 1024],
            ['a file bigger than the server shows', 'besar.pdf', 6 * 1024 * 1024],
        ])('offers only the download for %s', async (_case, name, size) => {
            show({ files: only(name, size) });

            await userEvent.setup().click(screen.getByText(name));

            expect(screen.getByText('Berkas ini hanya bisa diunduh; tidak bisa dilihat di layar.')).toBeInTheDocument();
            expect(screen.queryByTitle(`Pratinjau ${name}`)).not.toBeInTheDocument();
            expect(screen.getByRole('button', { name: 'Unduh' })).toBeInTheDocument();
        });
    });

    it('gives a folder only the delete action, and a file the others too', async () => {
        const user = userEvent.setup();
        show();

        await user.click(screen.getByRole('button', { name: 'Aksi Laporan' }));
        expect(screen.getAllByRole('menuitem').map((entry) => entry.textContent)).toEqual(['Hapus']);
        await user.keyboard('{Escape}');

        await user.click(screen.getByRole('button', { name: 'Aksi ringkasan.pdf' }));
        expect(screen.getAllByRole('menuitem').map((entry) => entry.textContent)).toEqual(['Detail', 'Rename', 'Unduh', 'Hapus']);
    });

    it('deletes after the confirmation, naming the path to the server', async () => {
        const user = userEvent.setup();
        show();

        await user.click(screen.getByRole('button', { name: 'Aksi ringkasan.pdf' }));
        await user.click(screen.getByRole('menuitem', { name: 'Hapus' }));
        expect(inertia.delete).not.toHaveBeenCalled();
        await user.click(within(screen.getByRole('alertdialog')).getByRole('button', { name: 'Hapus' }));

        expect(inertia.delete).toHaveBeenCalledWith('/storage/s3/files', expect.objectContaining({ data: { path: 'ringkasan.pdf' }, preserveScroll: true }));
    });

    it('shows the reason instead of a list when the storage cannot be used, and offers nothing to change', () => {
        show({ files: null, error: 'Konfigurasi penyimpanan belum lengkap.' });

        expect(screen.getByRole('alert')).toHaveTextContent('Konfigurasi penyimpanan belum lengkap.');
        expect(screen.queryByRole('table')).not.toBeInTheDocument();
        expect(screen.getByRole('button', { name: 'Folder Baru' })).toBeDisabled();
        expect(screen.getByRole('button', { name: 'Unggah' })).toBeDisabled();
    });

    it('says when a folder is bigger than what is shown', () => {
        show({ truncated: true });

        expect(screen.getByText(/lebih banyak entri dari yang bisa ditampilkan/)).toBeInTheDocument();
    });

    it('says when a folder is empty', () => {
        show({ files: { ...files, data: [], meta: { ...files.meta, total: 0, from: null, to: null } } });

        expect(screen.getByText('Folder ini kosong')).toBeInTheDocument();
    });
});
