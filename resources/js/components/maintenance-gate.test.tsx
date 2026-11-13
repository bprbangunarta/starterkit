import { act, render, screen } from '@testing-library/react';
import { beforeEach, describe, expect, it, vi } from 'vitest';
import { MaintenanceGate } from '@/components/maintenance-gate';

type Handler = (event: { detail: { response: { status: number } }; preventDefault: () => void }) => void;

const listeners = vi.hoisted(() => ({ handlers: [] as unknown[] }));

vi.mock('@inertiajs/react', async (importOriginal) => ({
    ...(await importOriginal<Record<string, unknown>>()),
    Head: () => null,
    router: {
        on: (_name: string, handler: unknown) => {
            listeners.handlers.push(handler);

            return () => undefined;
        },
    },
}));

/** What Inertia does when a request is answered with a page that is not an Inertia response. */
const answer = (status: number): (() => void) => {
    const preventDefault = vi.fn();

    act(() => void (listeners.handlers.at(-1) as Handler)({ detail: { response: { status } }, preventDefault }));

    return preventDefault;
};

const renderGate = () =>
    render(
        <MaintenanceGate>
            <p>aplikasi</p>
        </MaintenanceGate>,
    );

describe('MaintenanceGate', () => {
    beforeEach(() => {
        listeners.handlers = [];
    });

    it('shows the app until the server says it is in maintenance', () => {
        renderGate();

        expect(screen.getByText('aplikasi')).toBeInTheDocument();
        expect(screen.queryByRole('heading', { name: 'Sedang dalam pemeliharaan' })).not.toBeInTheDocument();
    });

    it('replaces the app with the full-screen maintenance page on a 503, and keeps Inertia from opening its own error modal', () => {
        renderGate();

        const preventDefault = answer(503);

        expect(preventDefault).toHaveBeenCalled();
        expect(screen.getByRole('heading', { name: 'Sedang dalam pemeliharaan' })).toBeInTheDocument();
        expect(screen.queryByText('aplikasi')).not.toBeInTheDocument();
        expect(screen.getByRole('button', { name: 'Coba lagi' })).toBeInTheDocument();
    });

    it('leaves every other status to the screens that handle it', () => {
        renderGate();

        const preventDefault = answer(500);

        expect(preventDefault).not.toHaveBeenCalled();
        expect(screen.getByText('aplikasi')).toBeInTheDocument();
    });
});
