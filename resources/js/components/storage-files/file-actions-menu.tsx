import { Download, Eye, Pencil, Trash2 } from 'lucide-react';
import type { StorageItem } from '@/components/storage-files/types';
import { RowActions } from '@/components/ui/row-actions';

type Props = {
    item: StorageItem;
    onDownload: (item: StorageItem) => void;
    onDetail: (item: StorageItem) => void;
    onRename: (item: StorageItem) => void;
    onRemove: (item: StorageItem) => void;
};

/** The actions of one folder or file. A folder can only be deleted (when it is empty); a file can also be taken, looked at, and renamed. */
export function FileActionsMenu({ item, onDownload, onDetail, onRename, onRemove }: Props) {
    const remove = { label: 'Hapus', icon: <Trash2 />, danger: true, onSelect: () => onRemove(item) };

    return (
        <RowActions
            title={item.name}
            actions={
                item.type === 'file'
                    ? [
                          { label: 'Detail', icon: <Eye />, onSelect: () => onDetail(item) },
                          { label: 'Rename', icon: <Pencil />, onSelect: () => onRename(item) },
                          { label: 'Unduh', icon: <Download />, onSelect: () => onDownload(item) },
                          remove,
                      ]
                    : [remove]
            }
        />
    );
}
