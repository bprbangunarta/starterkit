import { useState } from 'react';
import type { FilesLimits, PreviewKind, StorageItem } from '@/components/storage-files/types';
import { preview } from '@/routes/storage/files';

/** How a file can be shown in the page, or null when it is only for download: the extension is not on the server's list, or the file is bigger than the server allows. */
export function previewKindOf(item: StorageItem, limits: FilesLimits): PreviewKind | null {
    const extension = item.name.split('.').pop()?.toLowerCase() ?? '';
    const kind = item.type === 'file' ? limits.preview[extension] : undefined;

    if (kind === undefined || (item.size !== null && item.size > limits.preview_max_mb * 1024 * 1024)) {
        return null;
    }

    return kind;
}

type Props = { storage: string; item: StorageItem; kind: PreviewKind };

/** The file itself, read from the storage by the server and shown in the page: an image, or a PDF or text in a frame. */
export function FilePreview({ storage, item, kind }: Props) {
    const [failed, setFailed] = useState(false);
    const src = preview(storage, { query: { path: item.path } }).url;

    if (failed) {
        return <p className="text-xs text-muted">Berkas tidak bisa dimuat. Coba unduh.</p>;
    }

    if (kind === 'image') {
        return (
            <img src={src} alt={item.name} onError={() => setFailed(true)} className="max-h-96 w-full rounded-md border border-line bg-canvas object-contain" />
        );
    }

    return (
        <iframe
            src={src}
            title={`Pratinjau ${item.name}`}
            onError={() => setFailed(true)}
            className="h-[28rem] w-full rounded-md border border-line bg-surface"
        />
    );
}
