import { router, usePage } from '@inertiajs/react';
import { createContext, useContext, useEffect, useState } from 'react';
import type { ReactNode } from 'react';
import { toast } from 'sonner';
import { ReauthDialog } from '@/components/reauth/reauth-dialog';

type Shared = { auth: { user: { confirmed_until: number } | null } };

type Reauth = {
    /** Runs a sensitive action while the password confirmation still holds; otherwise asks for the password first, then runs it. */
    confirmed: (action: () => void) => void;
};

/** `action` is what to run after the password is confirmed; null when the server asked (423) after the action had already been sent. */
type Pending = { action: (() => void) | null };

const ReauthContext = createContext<Reauth | null>(null);

/**
 * One confirmation modal for the whole app. A page asks `confirmed(action)` before a sensitive action. The server checks again on every
 * such request: if the confirmation ran out meanwhile it answers 423, and the same modal opens and the person repeats the action.
 */
export function ReauthProvider({ children }: { children: ReactNode }) {
    const confirmedUntil = usePage<Shared>().props.auth.user?.confirmed_until ?? 0;
    const [pending, setPending] = useState<Pending | null>(null);

    useEffect(
        () =>
            router.on('httpException', (event) => {
                if (event.detail.response.status === 423) {
                    event.preventDefault();
                    setPending({ action: null });
                }
            }),
        [],
    );

    const confirmed = (action: () => void) => {
        if (confirmedUntil * 1000 > Date.now()) {
            action();

            return;
        }

        setPending({ action });
    };

    const done = () => {
        if (pending?.action) {
            pending.action();
        } else {
            toast.success('Kata sandi dikonfirmasi. Ulangi aksi Anda.');
        }

        setPending(null);
    };

    return (
        <ReauthContext.Provider value={{ confirmed }}>
            {children}
            {pending && <ReauthDialog onClose={() => setPending(null)} onConfirmed={done} />}
        </ReauthContext.Provider>
    );
}

export function useReauth(): Reauth {
    const context = useContext(ReauthContext);

    if (!context) {
        throw new Error('useReauth needs a ReauthProvider above it.');
    }

    return context;
}
