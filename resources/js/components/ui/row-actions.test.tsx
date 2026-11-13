import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { Pencil, Trash2 } from 'lucide-react';
import { beforeAll, describe, expect, it, vi } from 'vitest';
import { RowActions } from '@/components/ui/row-actions';
import { TooltipProvider } from '@/components/ui/tooltip';

describe('RowActions', () => {
    beforeAll(() => {
        window.ResizeObserver = class {
            observe() {}
            unobserve() {}
            disconnect() {}
        };
    });

    const show = (onEdit = vi.fn(), onRemove = vi.fn()) =>
        render(
            <TooltipProvider>
                <RowActions
                    title="Proyek Alfa"
                    heading="Proyek Alfa"
                    actions={[
                        { label: 'Ubah', icon: <Pencil />, onSelect: onEdit },
                        { label: 'Hapus', icon: <Trash2 />, danger: true, disabled: true, reason: 'Masih dipakai', onSelect: onRemove },
                    ]}
                />
            </TooltipProvider>,
        );

    it('names the button and the menu after the row, and lists the actions with the destructive one below a separator', async () => {
        show();

        await userEvent.setup().click(screen.getByRole('button', { name: 'Aksi Proyek Alfa' }));

        expect(screen.getByText('Proyek Alfa')).toBeInTheDocument();
        expect(screen.getAllByRole('menuitem').map((item) => item.textContent)).toEqual(['Ubah', 'Hapus']);
        expect(screen.getByRole('separator')).toBeInTheDocument();
    });

    it('shows no heading when none is given, and the button keeps its name', async () => {
        render(
            <TooltipProvider>
                <RowActions title="Proyek Alfa" actions={[{ label: 'Ubah', icon: <Pencil />, onSelect: vi.fn() }]} />
            </TooltipProvider>,
        );

        await userEvent.setup().click(screen.getByRole('button', { name: 'Aksi Proyek Alfa' }));

        expect(screen.queryByText('Proyek Alfa')).not.toBeInTheDocument();
    });

    it('runs the chosen action, and not one that is not available, which says why', async () => {
        const onEdit = vi.fn();
        const onRemove = vi.fn();
        const user = userEvent.setup();
        show(onEdit, onRemove);

        await user.click(screen.getByRole('button', { name: 'Aksi Proyek Alfa' }));
        const remove = screen.getByRole('menuitem', { name: 'Hapus' });
        expect(remove).toHaveAttribute('title', 'Masih dipakai');
        await user.click(remove);
        expect(onRemove).not.toHaveBeenCalled();

        await user.click(screen.getByRole('menuitem', { name: 'Ubah' }));
        expect(onEdit).toHaveBeenCalledOnce();
    });
});
