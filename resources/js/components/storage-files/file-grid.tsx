import type { CSSProperties, ReactNode } from 'react';
import { kindOf } from '@/components/storage-files/items';
import type { StorageItem } from '@/components/storage-files/types';
import { formatBytes } from '@/lib/format';
import { cn } from '@/lib/utils';

type Props = {
    items: StorageItem[];
    actions: (item: StorageItem) => ReactNode;
    onOpen: (item: StorageItem) => void;
};

/** The grid view: one card per folder or file, with its menu in the corner. */
export function FileGrid({ items, actions, onOpen }: Props) {
    return (
        <ul className="grid grid-cols-2 gap-2 p-3 sm:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5">
            {items.map((item, index) => {
                const { icon: Icon, label } = kindOf(item);

                return (
                    <li
                        key={item.path}
                        className="motion-item relative flex flex-col rounded-lg border border-line bg-surface p-3 transition-[background-color,transform,box-shadow] duration-200 ease-out hover:-translate-y-0.5 hover:bg-canvas hover:shadow-sm"
                        style={{ '--i': Math.min(index, 12) } as CSSProperties}
                    >
                        <div className="absolute top-1 right-1">{actions(item)}</div>
                        <button
                            type="button"
                            onClick={() => onOpen(item)}
                            className="flex min-w-0 flex-col items-start gap-2 rounded text-left focus-visible:ring-2 focus-visible:ring-primary/30 focus-visible:outline-none"
                        >
                            <Icon className={cn('size-9', item.type === 'folder' ? 'text-primary' : 'text-muted')} />
                            <span className="w-full min-w-0">
                                <span className="block truncate text-sm font-medium" title={item.name}>
                                    {item.name}
                                </span>
                                <span className="block truncate text-xs text-muted">
                                    {item.type === 'folder' || item.size === null ? 'Folder' : `${label} · ${formatBytes(item.size)}`}
                                </span>
                            </span>
                        </button>
                    </li>
                );
            })}
        </ul>
    );
}
