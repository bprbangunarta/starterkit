import { toQuery } from '@/components/storage-files/files-query';
import type { FilesFilters } from '@/components/storage-files/types';
import { useListNavigation } from '@/hooks/use-list-navigation';
import { files } from '@/routes/storage';

const RELOADED = ['files', 'filters', 'path', 'truncated', 'error'];

/** Reloads the list of one storage with changed filters or another folder (a folder is a filter: `path`). */
export function useFilesNavigation(storage: string, filters: FilesFilters) {
    return useListNavigation(filters, { url: files(storage).url, toQuery, only: RELOADED });
}
