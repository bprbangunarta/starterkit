import { Link } from '@inertiajs/react';
import { Cloud, Folder, FolderOpen, HardDrive, Pencil, PlugZap, RotateCcw, Tag } from 'lucide-react';
import type { StorageEntry } from '@/components/storage/types';
import { Button, buttonVariants } from '@/components/ui/button';
import type { Column } from '@/components/ui/data-table';
import { Badge } from '@/components/ui/misc';
import { RowActions } from '@/components/ui/row-actions';
import type { RowAction } from '@/components/ui/row-actions';
import { Tip } from '@/components/ui/tooltip';
import { formatRelative } from '@/lib/format';
import { files } from '@/routes/storage';

type Actions = {
    testing: string | null;
    onEdit: (entry: StorageEntry) => void;
    onRename: (entry: StorageEntry) => void;
    onTest: (entry: StorageEntry) => void;
    onReset: (entry: StorageEntry) => void;
};

function StorageIcon({ type }: { type: StorageEntry['type'] }) {
    const Icon = { local: Folder, s3: Cloud, sftp: HardDrive }[type];

    return <Icon className="size-4 shrink-0 text-primary" />;
}

function SourceBadge({ entry }: { entry: StorageEntry }) {
    if (!entry.editable) {
        return <Badge tone="neutral">Proyek</Badge>;
    }

    return <Badge tone={entry.source === 'screen' ? 'info' : 'neutral'}>{entry.source === 'screen' ? 'Layar' : '.env'}</Badge>;
}

function TestResult({ entry }: { entry: StorageEntry }) {
    if (entry.tested_at === null || entry.test_ok === null) {
        return '–';
    }

    return (
        <Tip label={entry.test_message ?? ''}>
            <span className="flex items-center gap-2">
                <Badge tone={entry.test_ok ? 'success' : 'danger'}>{entry.test_ok ? 'Berhasil' : 'Gagal'}</Badge>
                <span className="text-xs text-muted">{formatRelative(entry.tested_at)}</span>
            </span>
        </Tip>
    );
}

/** Opening the files is the main thing to do with a storage, so it stays a button of its own beside the menu. */
function OpenFiles({ entry }: { entry: StorageEntry }) {
    return (
        <Tip label={entry.configured ? 'Buka berkas' : 'Lengkapi konfigurasi lebih dulu'}>
            {entry.configured ? (
                <Link href={files(entry.type).url} className={buttonVariants({ variant: 'ghost', size: 'icon' })} aria-label={`Buka berkas ${entry.label}`}>
                    <FolderOpen />
                </Link>
            ) : (
                <Button variant="ghost" size="icon" aria-label={`Buka berkas ${entry.label}`} disabled>
                    <FolderOpen />
                </Button>
            )}
        </Tip>
    );
}

function storageActions(entry: StorageEntry, { testing, onEdit, onRename, onTest, onReset }: Actions): RowAction[] {
    const rename: RowAction = { label: 'Rename', icon: <Tag />, onSelect: () => onRename(entry) };
    const connection: RowAction = {
        label: testing === entry.type ? 'Menguji…' : 'Koneksi',
        icon: <PlugZap />,
        disabled: !entry.configured || testing === entry.type,
        reason: 'Lengkapi konfigurasi lebih dulu',
        onSelect: () => onTest(entry),
    };

    if (!entry.editable) {
        return [rename, connection];
    }

    const actions: RowAction[] = [rename, { label: 'Konfig', icon: <Pencil />, onSelect: () => onEdit(entry) }, connection];

    if (entry.source === 'screen') {
        actions.push({ label: 'Restore', icon: <RotateCcw />, onSelect: () => onReset(entry) });
    }

    return actions;
}

export function storageColumns(actions: Actions): Column<StorageEntry>[] {
    return [
        {
            key: 'name',
            header: 'Penyimpanan',
            className: 'font-medium',
            cell: (entry) => (
                <span className="flex items-center gap-2">
                    <StorageIcon type={entry.type} />
                    {entry.label}
                    {entry.active && <Badge tone="success">Aktif</Badge>}
                    {entry.name !== null && <span className="text-xs font-normal text-muted">{entry.kind}</span>}
                </span>
            ),
        },
        { key: 'source', header: 'Sumber', cell: (entry) => <SourceBadge entry={entry} /> },
        {
            key: 'summary',
            header: 'Konfigurasi',
            cell: (entry) => (entry.configured ? entry.summary : <Badge tone="warning">Belum lengkap</Badge>),
        },
        { key: 'test', header: 'Uji terakhir', cell: (entry) => <TestResult entry={entry} /> },
        {
            key: 'actions',
            header: 'Aksi',
            srOnly: true,
            narrow: true,
            align: 'right',
            cell: (entry) => (
                <span className="flex justify-end gap-1">
                    <RowActions title={entry.label} actions={storageActions(entry, actions)} />
                    <OpenFiles entry={entry} />
                </span>
            ),
        },
    ];
}
