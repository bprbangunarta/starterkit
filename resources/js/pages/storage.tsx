import { Head, router } from '@inertiajs/react';
import { HardDrive } from 'lucide-react';
import { useState } from 'react';
import type { ReactNode } from 'react';
import { toast } from 'sonner';
import { useReauth } from '@/components/reauth/reauth-provider';
import { StorageNameDialog } from '@/components/storage/storage-name-dialog';
import { S3FormDialog } from '@/components/storage/s3-form-dialog';
import { SftpFormDialog } from '@/components/storage/sftp-form-dialog';
import { storageColumns } from '@/components/storage/storage-columns';
import type { StorageEntry } from '@/components/storage/types';
import { DataTable } from '@/components/ui/data-table';
import { ConfirmDialog } from '@/components/ui/dialog';
import { PageHeader } from '@/components/ui/misc';
import AppLayout from '@/layouts/app-layout';
import { destroy } from '@/routes/storage';
import { store } from '@/routes/storage/test';

type Props = { storages: StorageEntry[] };

/** The local storage, Amazon S3, and SFTP: the connection in force for each, the result of the last test, and the actions on them. */
export default function Storage({ storages }: Props) {
    const [editing, setEditing] = useState<StorageEntry | null>(null);
    const [renaming, setRenaming] = useState<StorageEntry | null>(null);
    const [resetting, setResetting] = useState<StorageEntry | null>(null);
    const [testing, setTesting] = useState<string | null>(null);
    const { confirmed } = useReauth();

    const test = (entry: StorageEntry) =>
        router.post(
            store(entry.type).url,
            {},
            {
                preserveScroll: true,
                onStart: () => setTesting(entry.type),
                onFinish: () => setTesting(null),
                onSuccess: (page) => {
                    const result = (page.props as unknown as Props).storages.find((item) => item.type === entry.type);

                    if (result?.test_ok) {
                        toast.success(`${entry.label}: terhubung.`);
                    } else {
                        toast.error(result?.test_message ?? 'Uji koneksi gagal.');
                    }
                },
            },
        );

    const reset = (entry: StorageEntry) =>
        router.delete(destroy(entry.type).url, {
            preserveScroll: true,
            onSuccess: () => toast.success(`${entry.kind} kembali memakai bawaan .env.`),
            onFinish: () => setResetting(null),
        });

    return (
        <>
            <Head title="Penyimpanan" />
            <PageHeader title="Penyimpanan" description="Penyimpanan lokal proyek, S3, dan SFTP; S3 dan SFTP bawaan dari server, diganti bila diisi di sini" />
            <DataTable
                rows={storages}
                rowKey={(entry) => entry.type}
                columns={storageColumns({
                    testing,
                    onEdit: (entry) => confirmed(() => setEditing(entry)),
                    onRename: (entry) => confirmed(() => setRenaming(entry)),
                    onTest: (entry) => confirmed(() => test(entry)),
                    onReset: (entry) => confirmed(() => setResetting(entry)),
                })}
                empty={{ icon: <HardDrive />, title: 'Belum ada penyimpanan' }}
            />
            {renaming !== null && <StorageNameDialog entry={renaming} onClose={() => setRenaming(null)} />}
            {editing?.type === 's3' && <S3FormDialog entry={editing} onClose={() => setEditing(null)} />}
            {editing?.type === 'sftp' && <SftpFormDialog entry={editing} onClose={() => setEditing(null)} />}
            <ConfirmDialog
                open={resetting !== null}
                onOpenChange={(open) => !open && setResetting(null)}
                title="Kembali ke Bawaan?"
                description={resetting ? `Konfigurasi ${resetting.kind} yang disimpan di layar akan dihapus, dan bawaan dari .env dipakai lagi.` : ''}
                confirmLabel="Kembalikan"
                onConfirm={() => resetting && reset(resetting)}
            />
        </>
    );
}

Storage.layout = (page: ReactNode) => <AppLayout>{page}</AppLayout>;
