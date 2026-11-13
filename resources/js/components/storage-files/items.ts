import { File, FileArchive, FileImage, FileSpreadsheet, FileText, Folder } from 'lucide-react';
import type { LucideIcon } from 'lucide-react';
import type { StorageItem } from '@/components/storage-files/types';

const KINDS: { match: RegExp; label: string; icon: LucideIcon }[] = [
    { match: /\.(png|jpe?g|gif|webp|svg)$/i, label: 'Gambar', icon: FileImage },
    { match: /\.pdf$/i, label: 'PDF', icon: FileText },
    { match: /\.(docx?|txt|rtf)$/i, label: 'Dokumen', icon: FileText },
    { match: /\.(xlsx?|csv)$/i, label: 'Spreadsheet', icon: FileSpreadsheet },
    { match: /\.(zip|rar|7z|tar|gz)$/i, label: 'Arsip', icon: FileArchive },
];

/** Label and icon from the type or the file extension. */
export function kindOf(item: StorageItem): { label: string; icon: LucideIcon } {
    if (item.type === 'folder') {
        return { label: 'Folder', icon: Folder };
    }

    return KINDS.find((k) => k.match.test(item.name)) ?? { label: 'Berkas', icon: File };
}

/** The folders of a path from the top down, each with the path that opens it: "a/b" gives a and a/b. */
export function crumbsOf(path: string): { name: string; path: string }[] {
    const parts = path === '' ? [] : path.split('/');

    return parts.map((name, index) => ({ name, path: parts.slice(0, index + 1).join('/') }));
}
