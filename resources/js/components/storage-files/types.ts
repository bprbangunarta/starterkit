import type { ListFilters } from '@/lib/list';
import type { PageMeta } from '@/components/ui/pagination';

/** One folder or file of a storage, as the server lists it. Folders have no size, and a folder may have no date either. */
export type StorageItem = {
    name: string;
    path: string;
    type: 'folder' | 'file';
    size: number | null;
    modified: string | null;
};

export type PreviewKind = 'image' | 'pdf' | 'text';

/** What the server allows the file manager to take and to show; `preview` maps an extension to how it is shown. */
export type FilesLimits = { max_mb: number; extensions: string[]; preview: Record<string, PreviewKind>; preview_max_mb: number };

export type StorageRef = { type: 'local' | 's3' | 'sftp'; label: string };

export type FilesFilters = ListFilters & { path: string };

export type FilesPage = { data: StorageItem[]; meta: PageMeta };
