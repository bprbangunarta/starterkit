import { useState } from 'react';
import { toast } from 'sonner';
import { INITIAL_ROWS, pageOfRows } from '@/components/guideline/crud-data';
import type { Draft, Order, Row } from '@/components/guideline/crud-data';

export function useCrudState() {
    const [rows, setRows] = useState<Row[]>(INITIAL_ROWS);
    const [search, setSearch] = useState('');
    const [status, setStatus] = useState<string | null>(null);
    const [page, setPage] = useState(1);
    const [perPage, setPerPage] = useState(5);
    // A first click sorts ascending; clicking the active column flips the direction.
    const [order, setOrder] = useState<Order>({ sort: 'name', direction: 'asc' });
    const [draft, setDraft] = useState<Draft | null>(null);
    const [nameError, setNameError] = useState<string | undefined>();
    const [removing, setRemoving] = useState<Row | null>(null);

    const openDraft = (next: Draft) => {
        setNameError(undefined);
        setDraft(next);
    };

    const save = () => {
        if (!draft) {
            return;
        }

        if (draft.name.trim() === '') {
            setNameError('Nama wajib diisi.');

            return;
        }

        const next = { name: draft.name.trim(), status: draft.status, amount: Number(draft.amount) || 0 };
        setRows((all) =>
            draft.id === null
                ? [...all, { id: Math.max(0, ...all.map((r) => r.id)) + 1, ...next }]
                : all.map((r) => (r.id === draft.id ? { ...r, ...next } : r)),
        );
        toast.success(draft.id === null ? 'Data ditambahkan' : 'Perubahan tersimpan');
        setDraft(null);
    };

    const confirmRemove = () => {
        setRows((all) => all.filter((r) => r.id !== removing?.id));
        setRemoving(null);
        toast.success('Dihapus');
    };

    return {
        view: pageOfRows(rows, { search, status, order, page, perPage }),
        filters: { search, setSearch, status, setStatus, order, setOrder, perPage, setPerPage, setPage },
        form: { draft, setDraft, nameError, openDraft, save },
        removal: { removing, setRemoving, confirmRemove },
    };
}
