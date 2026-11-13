import { useSyncExternalStore } from 'react';
import { APP_LOGO, APP_NAME } from '@/lib/brand';

export type Brand = { name: string; logo: string };

let current: Brand = { name: APP_NAME, logo: APP_LOGO };
const listeners = new Set<() => void>();

export function getBrand(): Brand {
    return current;
}

/** Called on every page the server renders, so a name saved on the Pengaturan screen shows without a reload. */
export function setBrand(next: Brand): void {
    if (next.name === current.name && next.logo === current.logo) {
        return;
    }

    current = next;
    listeners.forEach((listener) => listener());
}

function subscribe(listener: () => void): () => void {
    listeners.add(listener);

    return () => listeners.delete(listener);
}

/** The brand as the project settings give it; the bundled name until the first page arrives, and in tests. */
export function useBrand(): Brand {
    return useSyncExternalStore(subscribe, getBrand);
}
