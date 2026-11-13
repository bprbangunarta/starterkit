import { render, screen } from '@testing-library/react';
import userEvent from '@testing-library/user-event';
import { useState } from 'react';
import { describe, expect, it } from 'vitest';
import { Field } from '@/components/ui/field';
import { Textarea } from '@/components/ui/textarea';

function Harness({ maxLength }: { maxLength?: number }) {
    const [value, setValue] = useState('');

    return (
        <Field label="Alamat">
            <Textarea maxLength={maxLength} value={value} onChange={(event) => setValue(event.target.value)} />
        </Field>
    );
}

describe('Textarea in a Field', () => {
    it('counts the characters against the limit while typing', async () => {
        render(<Harness maxLength={20} />);

        expect(screen.getByText('0/20')).toBeInTheDocument();

        await userEvent.type(screen.getByLabelText('Alamat'), 'Jl. Contoh 1');

        expect(screen.getByText('12/20')).toBeInTheDocument();
    });

    it('stops at the limit', async () => {
        render(<Harness maxLength={5} />);

        await userEvent.type(screen.getByLabelText('Alamat'), 'abcdefgh');

        expect(screen.getByLabelText('Alamat')).toHaveValue('abcde');
        expect(screen.getByText('5/5')).toBeInTheDocument();
    });

    it('shows no counter without a limit', () => {
        render(<Harness />);

        expect(screen.queryByText(/\/\d+/)).not.toBeInTheDocument();
    });
});
