import { useEffect, useState } from 'react';

const SEARCH_DELAY_MS = 300;

/** The text of a list's search box: typing is kept locally and committed to the list after a short pause. */
export function useDebouncedSearch(committed: string | null, onCommit: (value: string | null) => void) {
    const [search, setSearch] = useState(committed ?? '');

    useEffect(() => {
        if (search === (committed ?? '')) {
            return;
        }

        const timer = setTimeout(() => onCommit(search || null), SEARCH_DELAY_MS);

        return () => clearTimeout(timer);
    }, [search, committed, onCommit]);

    return [search, setSearch] as const;
}
