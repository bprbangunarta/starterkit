import { Head, router } from '@inertiajs/react';
import { FolderPlus, Upload } from 'lucide-react';
import { useState } from 'react';
import type { ReactNode } from 'react';
import { useReauth } from '@/components/reauth/reauth-provider';
import { FileActionsMenu } from '@/components/storage-files/file-actions-menu';
import { FilesDialogs } from '@/components/storage-files/files-dialogs';
import type { OpenFilesDialog } from '@/components/storage-files/files-dialogs';
import { FilesBrowser } from '@/components/storage-files/files-browser';
import type { FilesView } from '@/components/storage-files/files-browser';
import { storageCrumbs } from '@/components/storage-files/crumbs';
import type { FilesFilters, FilesLimits, FilesPage, StorageItem, StorageRef } from '@/components/storage-files/types';
import { useFilesNavigation } from '@/components/storage-files/use-files-navigation';
import { Breadcrumb } from '@/components/ui/breadcrumb';
import { Button } from '@/components/ui/button';
import { Alert, PageHeader } from '@/components/ui/misc';
import AppLayout from '@/layouts/app-layout';
import { download } from '@/routes/storage/files';
import { files as filesRoute } from '@/routes/storage';

type Props = {
    storage: StorageRef;
    path: string;
    files: FilesPage | null;
    filters: Omit<FilesFilters, 'path'>;
    truncated: boolean;
    error: string | null;
    limits: FilesLimits;
};

/** The folders and files of one storage, read from the storage itself: open folders, take files, make folders, upload, rename, and delete. */
export default function StorageFiles({ storage, path, files, filters, truncated, error, limits }: Props) {
    const [open, setOpen] = useState<OpenFilesDialog>(null);
    const [view, setView] = useState<FilesView>('grid');
    const { loading, visit } = useFilesNavigation(storage.type, { ...filters, path });
    const { confirmed } = useReauth();
    const close = () => setOpen(null);

    const openItem = (item: StorageItem) => {
        if (item.type === 'file') {
            setOpen({ kind: 'detail', item });

            return;
        }

        router.get(filesRoute(storage.type, { query: { path: item.path } }).url);
    };

    const takeFile = (target: StorageItem) => window.location.assign(download(storage.type, { query: { path: target.path } }).url);

    const actions = (item: StorageItem): ReactNode => (
        <FileActionsMenu
            item={item}
            onDownload={takeFile}
            onDetail={(target) => setOpen({ kind: 'detail', item: target })}
            onRename={(target) => confirmed(() => setOpen({ kind: 'name', draft: { mode: 'rename', item: target } }))}
            onRemove={(target) => confirmed(() => setOpen({ kind: 'remove', item: target }))}
        />
    );

    return (
        <>
            <Head title={`Berkas ${storage.label}`} />
            <div className="mb-2">
                <Breadcrumb items={storageCrumbs(storage, path)} />
            </div>
            <PageHeader
                title={`Berkas ${storage.label}`}
                description={path === '' ? 'Isi penyimpanan, dibaca langsung dari penyimpanannya' : `/${path}`}
                actions={
                    <>
                        <Button
                            variant="outline"
                            disabled={files === null}
                            onClick={() => confirmed(() => setOpen({ kind: 'name', draft: { mode: 'create' } }))}
                        >
                            <FolderPlus /> Folder Baru
                        </Button>
                        <Button disabled={files === null} onClick={() => confirmed(() => setOpen({ kind: 'upload' }))}>
                            <Upload /> Unggah
                        </Button>
                    </>
                }
            />
            {error !== null && (
                <Alert tone="danger" className="mb-3">
                    {error}
                </Alert>
            )}
            {truncated && (
                <Alert tone="warning" className="mb-3">
                    Folder ini berisi lebih banyak entri dari yang bisa ditampilkan. Yang tampil hanya sebagian pertamanya; pecah isinya ke beberapa folder.
                </Alert>
            )}
            {files !== null && (
                <FilesBrowser
                    files={files}
                    filters={{ ...filters, path }}
                    loading={loading}
                    view={view}
                    onView={setView}
                    visit={visit}
                    actions={actions}
                    onOpen={openItem}
                />
            )}
            <FilesDialogs open={open} storage={storage.type} folder={path} limits={limits} onDownload={takeFile} onClose={close} />
        </>
    );
}

StorageFiles.layout = (page: ReactNode) => <AppLayout>{page}</AppLayout>;
