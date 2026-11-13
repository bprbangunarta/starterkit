import { render, screen, within } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { describe, expect, it, vi } from 'vitest';
import { AuditDetailDialog } from '@/components/audit/audit-detail-dialog';
import { auditEntry } from '@/test/audit-entry';

describe('AuditDetailDialog', () => {
    it('renders nothing while no entry is chosen', () => {
        render(<AuditDetailDialog entry={null} onClose={() => undefined} />);

        expect(screen.queryByRole('dialog')).not.toBeInTheDocument();
    });

    it('is titled by the entry number and the event', () => {
        render(<AuditDetailDialog entry={auditEntry()} onClose={() => undefined} />);

        expect(screen.getByRole('dialog', { name: 'Entri #1' })).toHaveAccessibleDescription('Diubah');
    });

    it('shows who did what, where from, and under which request', () => {
        render(<AuditDetailDialog entry={auditEntry()} onClose={() => undefined} />);

        const dialog = screen.getByRole('dialog');
        for (const text of [
            'Rina Wulandari · rina.w',
            'Pengguna',
            'Pengguna #12',
            'Pengguna diubah',
            '10.0.0.5',
            'POST /pengguna/12',
            'UjiBrowser/1.0',
            '3f1c2d7e-0000-4000-8000-000000000001',
        ]) {
            expect(within(dialog).getAllByText(text).length).toBeGreaterThan(0);
        }
    });

    it('shows the reason of the change only when there is one', () => {
        const { rerender } = render(<AuditDetailDialog entry={auditEntry()} onClose={() => undefined} />);

        expect(screen.queryByText('Alasan')).not.toBeInTheDocument();

        rerender(<AuditDetailDialog entry={auditEntry({ reason: 'Petugas baru bagian peninjauan' })} onClose={() => undefined} />);

        expect(screen.getByText('Alasan')).toBeInTheDocument();
        expect(screen.getByText('Petugas baru bagian peninjauan')).toBeInTheDocument();
    });

    it('lists the facts of an entry that has no before and after, such as an export request', () => {
        const details = [
            { label: 'Jumlah baris', value: '120' },
            { label: 'Memakai pencarian', value: 'Ya' },
        ];

        render(<AuditDetailDialog entry={auditEntry({ details })} onClose={() => undefined} />);

        expect(screen.getByText('Jumlah baris')).toBeInTheDocument();
        expect(screen.getByText('120')).toBeInTheDocument();
        expect(screen.getByText('Memakai pencarian')).toBeInTheDocument();
    });

    it('lists the before and after of every changed column, with hidden values left hidden', () => {
        render(<AuditDetailDialog entry={auditEntry()} onClose={() => undefined} />);

        const usernameRow = screen.getByRole('row', { name: /Username/ });
        expect(within(usernameRow).getByText('lama')).toBeInTheDocument();
        expect(within(usernameRow).getByText('baru')).toBeInTheDocument();
        expect(within(screen.getByRole('row', { name: /Kata sandi/ })).getAllByText('[disembunyikan]')).toHaveLength(2);
    });

    it('says so when the entry changed no data, and names a visitor without an account', () => {
        render(<AuditDetailDialog entry={auditEntry({ changes: { old: [], attributes: [] }, causer: null, subject: null })} onClose={() => undefined} />);

        expect(screen.getByText('Tidak ada perubahan data pada catatan ini.')).toBeInTheDocument();
        expect(screen.getByText('Tamu')).toBeInTheDocument();
    });

    it('closes from the corner button, and has no footer button repeating it', async () => {
        const onClose = vi.fn();
        render(<AuditDetailDialog entry={auditEntry()} onClose={onClose} />);
        const closeButtons = screen.getAllByRole('button', { name: 'Tutup' });

        expect(closeButtons).toHaveLength(1);

        await userEvent.click(closeButtons[0]);

        expect(onClose).toHaveBeenCalled();
    });
});
