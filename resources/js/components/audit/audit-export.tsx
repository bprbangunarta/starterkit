import { router } from '@inertiajs/react';
import { Download } from 'lucide-react';
import { useState } from 'react';
import { toast } from 'sonner';
import { toQuery } from '@/components/audit/audit-query';
import type { AuditFilters } from '@/components/audit/types';
import { Button } from '@/components/ui/button';
import { Tip } from '@/components/ui/tooltip';
import { formatNumber } from '@/lib/format';
import { store } from '@/routes/audit-logs/exports';

/**
 * Asks the server to build the file for the filters of the page (every matching row, not only this page) and returns at once;
 * the queue writes it and the bell says when it is ready.
 */
export function ExportButton({ filters, total }: { filters: AuditFilters; total: number }) {
    const [asking, setAsking] = useState(false);

    return (
        <Tip label="Mengekspor semua baris yang cocok dengan filter, bukan hanya halaman ini">
            <Button
                loading={asking}
                onClick={() =>
                    router.post(store().url, toQuery(filters), {
                        preserveScroll: true,
                        preserveState: true,
                        onStart: () => setAsking(true),
                        onFinish: () => setAsking(false),
                        onSuccess: () =>
                            toast.success(
                                `Ekspor ${formatNumber(total)} baris sesuai filter sedang disiapkan. Anda akan diberi tahu di lonceng notifikasi saat berkas siap.`,
                            ),
                        onError: (errors) => toast.error(errors.export ?? 'Ekspor tidak dapat diminta. Coba lagi.'),
                    })
                }
            >
                <Download /> Export
            </Button>
        </Tip>
    );
}
