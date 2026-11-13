import { act, render, screen } from '@testing-library/react';
import { afterEach, beforeEach, describe, expect, it, vi } from 'vitest';
import { IdleTimeout } from '@/components/idle-timeout';

const inertia = vi.hoisted(() => ({ idleMinutes: 10, post: vi.fn(), reload: vi.fn() }));

vi.mock('@inertiajs/react', async (importOriginal) => ({
    ...(await importOriginal<Record<string, unknown>>()),
    usePage: () => ({ props: { session: { idle_minutes: inertia.idleMinutes } }, url: '/' }),
    router: { post: inertia.post, reload: inertia.reload },
}));

const MINUTE = 60_000;

describe('IdleTimeout', () => {
    beforeEach(() => {
        vi.useFakeTimers();
        inertia.idleMinutes = 10;
        inertia.post.mockReset();
        inertia.reload.mockReset();
    });

    afterEach(() => {
        vi.useRealTimers();
    });

    it('stays quiet while the person has been idle for less than nine minutes', () => {
        render(<IdleTimeout />);

        act(() => void vi.advanceTimersByTime(8 * MINUTE));

        expect(screen.queryByText('Sesi Akan Berakhir')).not.toBeInTheDocument();
    });

    it('warns one minute before the end and counts the seconds', () => {
        render(<IdleTimeout />);

        act(() => void vi.advanceTimersByTime(9 * MINUTE + 1000));

        expect(screen.getByText('Sesi Akan Berakhir')).toBeInTheDocument();
        expect(screen.getByText(/59 detik/)).toBeInTheDocument();
    });

    it('signs out when the time is up', () => {
        render(<IdleTimeout />);

        act(() => void vi.advanceTimersByTime(10 * MINUTE + 1000));

        expect(inertia.post).toHaveBeenCalledTimes(1);
        expect(inertia.post).toHaveBeenCalledWith('/logout', {}, expect.objectContaining({ onFinish: expect.any(Function) as unknown }));
    });

    it('lets the person stay signed in from the warning, and asks the server so its own clock restarts', () => {
        render(<IdleTimeout />);
        act(() => void vi.advanceTimersByTime(9 * MINUTE + 1000));

        act(() => void screen.getByRole('button', { name: 'Tetap masuk' }).click());

        expect(inertia.reload).toHaveBeenCalledWith({ only: ['auth'] });
        expect(screen.queryByText('Sesi Akan Berakhir')).not.toBeInTheDocument();

        act(() => void vi.advanceTimersByTime(8 * MINUTE));

        expect(screen.queryByText('Sesi Akan Berakhir')).not.toBeInTheDocument();
        expect(inertia.post).not.toHaveBeenCalled();
    });

    it('lets the person sign out from the warning', () => {
        render(<IdleTimeout />);
        act(() => void vi.advanceTimersByTime(9 * MINUTE + 1000));

        act(() => void screen.getByRole('button', { name: 'Keluar' }).click());

        expect(inertia.post).toHaveBeenCalledTimes(1);
    });

    it('treats activity as a fresh start', () => {
        render(<IdleTimeout />);

        act(() => void vi.advanceTimersByTime(8 * MINUTE));
        act(() => void window.dispatchEvent(new Event('keydown')));
        act(() => void vi.advanceTimersByTime(8 * MINUTE));

        expect(screen.queryByText('Sesi Akan Berakhir')).not.toBeInTheDocument();
        expect(inertia.post).not.toHaveBeenCalled();
    });

    it('does nothing when the server gives no idle limit', () => {
        inertia.idleMinutes = 0;
        render(<IdleTimeout />);

        act(() => void vi.advanceTimersByTime(60 * MINUTE));

        expect(screen.queryByText('Sesi Akan Berakhir')).not.toBeInTheDocument();
        expect(inertia.post).not.toHaveBeenCalled();
    });
});
