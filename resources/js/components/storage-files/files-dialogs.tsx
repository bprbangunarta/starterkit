import { DetailDrawer } from '@/components/storage-files/detail-drawer';
import { NameDialog } from '@/components/storage-files/name-dialog';
import type { NameDraft } from '@/components/storage-files/name-dialog';
import { RemoveDialog } from '@/components/storage-files/remove-dialog';
import type { FilesLimits, StorageItem } from '@/components/storage-files/types';
import { UploadDialog } from '@/components/storage-files/upload-dialog';

/** The one dialog or drawer that is open, with what it works on. */
export type OpenFilesDialog =
    { kind: 'name'; draft: NameDraft } | { kind: 'upload' } | { kind: 'remove'; item: StorageItem } | { kind: 'detail'; item: StorageItem } | null;

type Props = {
    open: OpenFilesDialog;
    storage: string;
    folder: string;
    limits: FilesLimits;
    onDownload: (item: StorageItem) => void;
    onClose: () => void;
};

export function FilesDialogs({ open, storage, folder, limits, onDownload, onClose }: Props) {
    switch (open?.kind) {
        case 'name':
            return <NameDialog storage={storage} folder={folder} draft={open.draft} onClose={onClose} />;
        case 'upload':
            return <UploadDialog storage={storage} folder={folder} maxMb={limits.max_mb} extensions={limits.extensions} onClose={onClose} />;
        case 'remove':
            return <RemoveDialog storage={storage} item={open.item} onClose={onClose} />;
        case 'detail':
            return <DetailDrawer storage={storage} item={open.item} limits={limits} onDownload={onDownload} onClose={onClose} />;
        default:
            return null;
    }
}
