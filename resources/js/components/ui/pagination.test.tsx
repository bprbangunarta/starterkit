import { render, screen } from '@testing-library/react';
import { describe, expect, it } from 'vitest';
import { Pagination } from '@/components/ui/pagination';
import type { PageMeta } from '@/components/ui/pagination';

const meta: PageMeta = { current_page: 2, last_page: 5, from: 26, to: 50, total: 120 };

function show(overrides: Partial<PageMeta> = {}) {
    render(<Pagination meta={{ ...meta, ...overrides }} perPage={25} perPageOptions={[10, 25, 50]} onPage={() => undefined} onPerPage={() => undefined} />);
}

const before = (first: HTMLElement, second: HTMLElement) => Boolean(first.compareDocumentPosition(second) & Node.DOCUMENT_POSITION_FOLLOWING);

describe('Pagination', () => {
    it('puts the page size first, then the count, then the page numbers', () => {
        show();

        const size = screen.getByRole('combobox', { name: 'Baris per halaman' });
        const count = screen.getByText('26–50 dari 120');
        const pages = screen.getByRole('navigation', { name: 'Halaman' });

        expect(before(size, count)).toBe(true);
        expect(before(count, pages)).toBe(true);
    });

    it('shows no page numbers when there is only one page', () => {
        show({ current_page: 1, last_page: 1, from: 1, to: 1, total: 1 });

        expect(screen.queryByRole('navigation', { name: 'Halaman' })).not.toBeInTheDocument();
        expect(screen.getByText('1–1 dari 1')).toBeInTheDocument();
    });

    it('says there are no results when the list is empty', () => {
        show({ current_page: 1, last_page: 1, from: null, to: null, total: 0 });

        expect(screen.getByText('0 hasil')).toBeInTheDocument();
    });
});
