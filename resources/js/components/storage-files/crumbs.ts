import { crumbsOf } from '@/components/storage-files/items';
import type { StorageRef } from '@/components/storage-files/types';
import { storage as storageList } from '@/routes';
import { files } from '@/routes/storage';

/** The breadcrumb of a place in a storage: the list of storages, the storage, then each folder down to the open one. */
export function storageCrumbs(storage: StorageRef, path: string): { label: string; to?: string }[] {
    const folders = crumbsOf(path);

    return [
        { label: 'Penyimpanan', to: storageList().url },
        { label: storage.label, to: path === '' ? undefined : files(storage.type).url },
        ...folders.map((folder, index) => ({
            label: folder.name,
            to: index < folders.length - 1 ? files(storage.type, { query: { path: folder.path } }).url : undefined,
        })),
    ];
}
