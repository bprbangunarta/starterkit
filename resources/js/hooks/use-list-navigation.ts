import { router } from '@inertiajs/react';
import { useCallback, useState } from 'react';

type Options<Filters> = {
    url: string;
    toQuery: (filters: Filters, page: number) => Record<string, string | number>;
    /** The props to reload; a module-level constant so `visit` keeps one identity. */
    only: string[];
};

/** Reloads a server-driven list with changed filters, keeping the page in place and exposing a loading state meanwhile. */
export function useListNavigation<Filters>(filters: Filters, { url, toQuery, only }: Options<Filters>) {
    const [loading, setLoading] = useState(false);

    const visit = useCallback(
        (patch: Partial<Filters>, page = 1) => {
            router.get(url, toQuery({ ...filters, ...patch }, page), {
                preserveState: true,
                preserveScroll: true,
                replace: true,
                only,
                onStart: () => setLoading(true),
                onFinish: () => setLoading(false),
            });
        },
        [filters, url, toQuery, only],
    );

    return { loading, visit };
}
