import { router } from '@inertiajs/react';
import { useEffect, useState } from 'react';
import type { ReactNode } from 'react';
import { ErrorPage } from '@/components/error-page';

/**
 * Shows the maintenance page when the server answers 503 (php artisan down), instead of Inertia's error modal on every screen.
 * "Coba lagi" reloads the page.
 */
export function MaintenanceGate({ children }: { children: ReactNode }) {
    const [down, setDown] = useState(false);

    useEffect(
        () =>
            router.on('httpException', (event) => {
                if (event.detail.response.status === 503) {
                    event.preventDefault();
                    setDown(true);
                }
            }),
        [],
    );

    return down ? <ErrorPage status={503} /> : children;
}
