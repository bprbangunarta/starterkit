import { router, usePage } from '@inertiajs/react';
import { AlertTriangle, Bell, CheckCheck, CheckCircle2, Info } from 'lucide-react';
import { Button } from '@/components/ui/button';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { formatRelative } from '@/lib/format';
import { cn } from '@/lib/utils';
import { read, readAll } from '@/routes/notifications';

type Notice = {
    id: string;
    title: string;
    body: string | null;
    level: keyof typeof LEVEL_ICONS;
    url: string | null;
    read: boolean;
    at: string;
};

export type Notifications = { unread: number; items: Notice[] };

const LEVEL_ICONS = {
    info: { icon: Info, className: 'text-info', label: 'Info' },
    success: { icon: CheckCircle2, className: 'text-success', label: 'Berhasil' },
    warning: { icon: AlertTriangle, className: 'text-warning', label: 'Peringatan' },
} as const;

/** Notification level as a small icon in the status colour, named in text for screen readers. */
function LevelIcon({ level }: { level: keyof typeof LEVEL_ICONS }) {
    const { icon: Icon, className, label } = LEVEL_ICONS[level];

    return (
        <>
            <Icon aria-hidden="true" className={cn('mt-0.5 size-3.5 shrink-0', className)} />
            <span className="sr-only">{label}: </span>
        </>
    );
}

/** Only paths inside this app are followed; anything else stored is shown as plain text. */
export function isInternalPath(url: string | null): url is string {
    return url !== null && /^\/(?![/\\])/.test(url);
}

function markRead(notice: Notice): void {
    if (!notice.read) {
        router.post(read(notice.id).url, {}, { preserveScroll: true, preserveState: true });
    }
}

function NoticeRow({ notice }: { notice: Notice }) {
    const content = (
        <div className="flex items-start gap-2">
            <LevelIcon level={notice.level} />
            <div className="min-w-0 flex-1">
                <p className={cn('text-sm', !notice.read && 'font-medium')}>{notice.title}</p>
                {notice.body && <p className="text-xs text-muted">{notice.body}</p>}
                <p className="mt-0.5 text-2xs text-muted">{formatRelative(notice.at)}</p>
            </div>
            {!notice.read && (
                <>
                    <span aria-hidden="true" className="mt-1.5 size-1.5 shrink-0 rounded-full bg-primary" />
                    <span className="sr-only">Belum dibaca</span>
                </>
            )}
        </div>
    );

    return (
        <li className="hover:bg-canvas">
            {isInternalPath(notice.url) ? (
                // A plain link: the target is a file download, which an Inertia visit would try to show as a page.
                <a href={notice.url} className="block px-3 py-2" onClick={() => markRead(notice)}>
                    {content}
                </a>
            ) : (
                <button type="button" className="block w-full cursor-pointer px-3 py-2 text-left" onClick={() => markRead(notice)}>
                    {content}
                </button>
            )}
        </li>
    );
}

export function NotificationBell() {
    const { unread, items } = usePage<{ notifications: Notifications }>().props.notifications;

    return (
        <Popover>
            <PopoverTrigger asChild>
                <Button variant="ghost" size="icon" aria-label={`Notifikasi${unread ? ` (${unread} belum dibaca)` : ''}`} className="relative">
                    <Bell />
                    {unread > 0 && (
                        <span className="absolute -top-0.5 -right-0.5 flex min-w-4 items-center justify-center rounded-full bg-danger px-1 text-2xs leading-4 font-semibold text-on-primary">
                            {unread > 9 ? '9+' : unread}
                        </span>
                    )}
                </Button>
            </PopoverTrigger>
            <PopoverContent align="end" aria-label="Notifikasi" className="w-80 p-0">
                <div className="flex items-center justify-between border-b border-line px-3 py-2">
                    <p className="text-sm font-semibold">Notifikasi</p>
                    {unread > 0 && (
                        <button
                            type="button"
                            onClick={() => router.post(readAll().url, {}, { preserveScroll: true, preserveState: true })}
                            className="flex cursor-pointer items-center gap-1 text-xs text-primary hover:underline"
                        >
                            <CheckCheck className="size-3.5" /> Tandai semua dibaca
                        </button>
                    )}
                </div>
                {items.length === 0 ? (
                    <p className="px-3 py-8 text-center text-xs text-muted">Tidak ada notifikasi baru.</p>
                ) : (
                    <ul className="max-h-80 divide-y divide-line overflow-auto">
                        {items.map((notice) => (
                            <NoticeRow key={notice.id} notice={notice} />
                        ))}
                    </ul>
                )}
            </PopoverContent>
        </Popover>
    );
}
