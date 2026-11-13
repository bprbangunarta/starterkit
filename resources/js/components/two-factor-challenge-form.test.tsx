import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { describe, expect, it, vi } from 'vitest';
import { TwoFactorChallengeForm } from '@/components/two-factor-challenge-form';
import type { TwoFactorChallengeProps } from '@/components/two-factor-challenge-form';

vi.mock('@inertiajs/react', async (importOriginal) => ({ ...(await importOriginal<Record<string, unknown>>()), Head: () => null }));

function setup(overrides: Partial<TwoFactorChallengeProps> = {}) {
    const props: TwoFactorChallengeProps = {
        method: 'totp',
        name: 'Rina',
        email: 'rina@example.test',
        wait: 0,
        onVerify: vi.fn(),
        onResend: vi.fn(),
        onCancel: vi.fn(),
        ...overrides,
    };

    render(<TwoFactorChallengeForm {...props} />);

    return props;
}

describe('TwoFactorChallengeForm', () => {
    it('asks for the authenticator code and sends it only once it has six digits', async () => {
        const user = userEvent.setup();
        const props = setup();
        const submit = screen.getByRole('button', { name: 'Verifikasi' });

        expect(screen.getByText('Halo Rina, masukkan kode dari aplikasi authenticator Anda.')).toBeInTheDocument();
        expect(submit).toBeDisabled();

        await user.type(screen.getByLabelText('Kode verifikasi'), '482915');
        await user.click(submit);

        expect(props.onVerify).toHaveBeenCalledWith('482915', false);
    });

    it('switches to a recovery code, empties the field, and tells the server how to read the code', async () => {
        const user = userEvent.setup();
        const props = setup();

        await user.type(screen.getByLabelText('Kode verifikasi'), '4829');
        await user.click(screen.getByRole('button', { name: 'Pakai kode pemulihan' }));

        expect(screen.getByLabelText('Kode pemulihan')).toHaveValue('');

        await user.type(screen.getByLabelText('Kode pemulihan'), 'abcde-12345');
        await user.click(screen.getByRole('button', { name: 'Verifikasi' }));

        expect(props.onVerify).toHaveBeenCalledWith('abcde-12345', true);
    });

    it('names the email address for an email code, and holds the resend button during the wait', async () => {
        const user = userEvent.setup();
        const props = setup({ method: 'email', wait: 30 });

        expect(screen.getByText('Halo Rina, masukkan kode yang kami kirim ke rina@example.test.')).toBeInTheDocument();
        expect(screen.getByRole('button', { name: 'Kirim kode baru dalam 30 dtk' })).toBeDisabled();
        expect(screen.queryByRole('button', { name: 'Pakai kode pemulihan' })).not.toBeInTheDocument();

        await user.click(screen.getByRole('button', { name: /Kembali ke halaman masuk/ }));

        expect(props.onCancel).toHaveBeenCalled();
    });

    it('shows the message of the server under the field', () => {
        setup({ error: 'Kode salah atau sudah kedaluwarsa.' });

        expect(screen.getByRole('alert')).toHaveTextContent('Kode salah atau sudah kedaluwarsa.');
    });
});
