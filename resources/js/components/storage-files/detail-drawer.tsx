import { Download } from 'lucide-react';
import { FilePreview, previewKindOf } from '@/components/storage-files/file-preview';
import { kindOf } from '@/components/storage-files/items';
import type { FilesLimits, StorageItem } from '@/components/storage-files/types';
import { Button } from '@/components/ui/button';
import { Drawer, DrawerBody } from '@/components/ui/drawer';
import { formatBytes, formatDateTime } from '@/lib/format';

type Props = { storage: string; item: StorageItem; limits: FilesLimits; onDownload: (item: StorageItem) => void; onClose: () => void };

/** A file in a drawer from the side: the file itself when the browser may show it, and its details. */
export function DetailDrawer({ storage, item, limits, onDownload, onClose }: Props) {
    const previewKind = previewKindOf(item, limits);
    const rows: [string, string][] = [
        ['Jenis', kindOf(item).label],
        ['Ukuran', item.size === null ? '–' : formatBytes(item.size)],
        ['Diubah', item.modified ? formatDateTime(item.modified) : '–'],
        ['Lokasi', `/${item.path}`],
    ];

    return (
        <Drawer open onOpenChange={(next) => !next && onClose()} title={item.name} description="Rincian berkas">
            <DrawerBody className="flex flex-col gap-4">
                {previewKind !== null ? (
                    <FilePreview key={item.path} storage={storage} item={item} kind={previewKind} />
                ) : (
                    <p className="text-xs text-muted">Berkas ini hanya bisa diunduh; tidak bisa dilihat di layar.</p>
                )}
                <dl className="grid grid-cols-[6rem_1fr] gap-x-3 gap-y-2 text-sm">
                    {rows.map(([label, value]) => (
                        <div key={label} className="contents">
                            <dt className="text-xs text-muted">{label}</dt>
                            <dd className="break-all">{value}</dd>
                        </div>
                    ))}
                </dl>
                <Button variant="outline" onClick={() => onDownload(item)}>
                    <Download />
                    Unduh
                </Button>
            </DrawerBody>
        </Drawer>
    );
}
