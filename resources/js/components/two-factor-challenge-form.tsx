import { ArrowLeft } from 'lucide-react';
import { useEffect, useState } from 'react';
import { AuthLayout } from '@/components/auth-layout';
import { Button } from '@/components/ui/button';
import { CodeInput } from '@/components/ui/code-input';
import { Field } from '@/components/ui/field';
import { Card } from '@/components/ui/misc';

export type TwoFactorChallengeProps = {
    method: 'totp' | 'email';
    name: string;
    email: string;
    /** Seconds before a new email code may be asked for. */
    wait: number;
    /** The server's message about the code, shown under the field. */
    error?: string;
    processing?: boolean;
    /** The recovery flag tells the server how to read the code. */
    onVerify: (code: string, recovery: boolean) => void;
    onResend: () => void;
    onCancel: () => void;
};

const description = ({ method, name, email }: Pick<TwoFactorChallengeProps, 'method' | 'name' | 'email'>, recovery: boolean): string => {
    if (method === 'email') {
        return `Halo ${name}, masukkan kode yang kami kirim ke ${email}.`;
    }

    return recovery ? `Halo ${name}, masukkan salah satu kode pemulihan Anda.` : `Halo ${name}, masukkan kode dari aplikasi authenticator Anda.`;
};

/** Counts the seconds down once a second, from the value the server gave. */
function useCountdown(start: number): [number, (seconds: number) => void] {
    const [seconds, setSeconds] = useState(start);

    useEffect(() => {
        if (seconds <= 0) {
            return;
        }

        const timer = setTimeout(() => setSeconds((left) => left - 1), 1000);

        return () => clearTimeout(timer);
    }, [seconds]);

    return [seconds, setSeconds];
}

type LinksProps = { method: 'totp' | 'email'; recovery: boolean; wait: number; onToggle: () => void; onResend: () => void; onCancel: () => void };

function ChallengeLinks({ method, recovery, wait, onToggle, onResend, onCancel }: LinksProps) {
    return (
        <div className="flex flex-col items-center gap-1.5 text-xs">
            {method === 'email' && (
                <button
                    type="button"
                    disabled={wait > 0}
                    onClick={onResend}
                    className="cursor-pointer text-primary hover:underline disabled:cursor-not-allowed disabled:text-muted disabled:no-underline"
                >
                    {wait > 0 ? `Kirim kode baru dalam ${wait} dtk` : 'Kirim kode baru'}
                </button>
            )}
            {method === 'totp' && (
                <button type="button" onClick={onToggle} className="cursor-pointer text-primary hover:underline">
                    {recovery ? 'Pakai aplikasi authenticator' : 'Pakai kode pemulihan'}
                </button>
            )}
            <button type="button" onClick={onCancel} className="flex cursor-pointer items-center gap-1 text-muted hover:text-ink">
                <ArrowLeft className="size-3" /> Kembali ke halaman masuk
            </button>
        </div>
    );
}

// TODO: the page that fills these props and the routes that verify, resend, and cancel come with two-factor sign-in (D-0022, postponed).
/** The second step of signing in: a one-time code from the authenticator app or the email, or a recovery code. */
export function TwoFactorChallengeForm({ method, name, email, wait: initialWait, error, processing, onVerify, onResend, onCancel }: TwoFactorChallengeProps) {
    const [code, setCode] = useState('');
    const [recovery, setRecovery] = useState(false);
    const [wait, setWait] = useCountdown(initialWait);

    return (
        <AuthLayout title="Verifikasi dua langkah" pageTitle="Verifikasi" illustration="verify" description={description({ method, name, email }, recovery)}>
            <Card className="p-4">
                <form
                    className="flex flex-col gap-3"
                    noValidate
                    onSubmit={(e) => {
                        e.preventDefault();
                        onVerify(code, recovery);
                    }}
                >
                    <Field label={recovery ? 'Kode pemulihan' : 'Kode verifikasi'} error={error}>
                        <CodeInput
                            autoFocus
                            recovery={recovery}
                            value={code}
                            onValueChange={setCode}
                            aria-invalid={!!error}
                            placeholder={recovery ? 'xxxxx-xxxxx' : '••••••'}
                        />
                    </Field>
                    <Button type="submit" loading={processing} disabled={code.length < (recovery ? 11 : 6)}>
                        Verifikasi
                    </Button>
                    <ChallengeLinks
                        method={method}
                        recovery={recovery}
                        wait={wait}
                        onToggle={() => {
                            setRecovery((current) => !current);
                            setCode('');
                        }}
                        onResend={() => {
                            onResend();
                            setWait(initialWait);
                        }}
                        onCancel={onCancel}
                    />
                </form>
            </Card>
        </AuthLayout>
    );
}
