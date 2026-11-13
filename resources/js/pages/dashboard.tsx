import { Head } from '@inertiajs/react';
import { LayoutDashboard } from 'lucide-react';
import type { ReactNode } from 'react';
import { Card, EmptyState, PageHeader } from '@/components/ui/misc';
import AppLayout from '@/layouts/app-layout';

// TODO: replace the empty state with the first real content of the dashboard once a credit feature exists.
export default function Dashboard() {
    return (
        <>
            <Head title="Dashboard" />
            <PageHeader title="Dashboard" description="Ringkasan akan tampil di sini" />
            <Card>
                <EmptyState icon={<LayoutDashboard />} title="Belum ada data" description="Ringkasan muncul setelah fitur pertama dibuat." />
            </Card>
        </>
    );
}

Dashboard.layout = (page: ReactNode) => <AppLayout>{page}</AppLayout>;
