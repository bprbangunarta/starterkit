import { render, screen } from '@testing-library/react';
import { describe, expect, it } from 'vitest';
import { auditColumns } from '@/components/audit/audit-columns';
import { TooltipProvider } from '@/components/ui/tooltip';
import { auditEntry } from '@/test/audit-entry';

const COLUMN = { time: 0, host: 1, user: 2, module: 3, event: 4, record: 5, outcome: 6 };

function cell(index: number, entry = auditEntry()) {
    const column = auditColumns()[index];

    if (!column) {
        throw new Error(`No column at ${index}`);
    }

    return render(<TooltipProvider>{column.cell(entry, 0)}</TooltipProvider>);
}

describe('auditColumns', () => {
    it('has one column per fact, in the order of the template', () => {
        expect(auditColumns().map((column) => column.key)).toEqual(['time', 'host', 'user', 'module', 'event', 'record', 'outcome']);
    });

    it('sorts every column', () => {
        expect(auditColumns().map((column) => column.sort)).toEqual(['time', 'host', 'user', 'module', 'event', 'record', 'outcome']);
    });

    it('keeps every cell to one line of text', () => {
        const texts = [COLUMN.time, COLUMN.host, COLUMN.user, COLUMN.module, COLUMN.event, COLUMN.record].map((index) => {
            const { container } = cell(index);

            return container.textContent;
        });

        expect(texts[1]).toBe('10.0.0.5');
        expect(texts[2]).toBe('Rina Wulandari');
        expect(texts[3]).toBe('Pengguna');
        expect(texts[4]).toBe('Diubah');
        expect(texts[5]).toBe('Pengguna #12');
        expect(texts[0]).toMatch(/2026/);
    });

    it('calls a visitor without an account Tamu, and a command or job without an address Sistem', () => {
        const visitor = cell(COLUMN.user, auditEntry({ causer: null }));
        expect(visitor.container.textContent).toBe('Tamu');
        visitor.unmount();

        const system = cell(COLUMN.user, auditEntry({ causer: null, ip_address: null }));
        expect(system.container.textContent).toBe('Sistem');
    });

    it('shows a dash for what an entry does not have', () => {
        expect(cell(COLUMN.host, auditEntry({ ip_address: null })).container.textContent).toBe('–');
        expect(cell(COLUMN.module, auditEntry({ module: null })).container.textContent).toBe('–');
        expect(cell(COLUMN.record, auditEntry({ subject: null })).container.textContent).toBe('–');
    });

    it('shows the result as a badge', () => {
        cell(COLUMN.outcome, auditEntry({ result: { value: 'failed', label: 'Gagal' } }));

        expect(screen.getByText('Gagal')).toBeInTheDocument();
    });
});
