import { Link, usePage, usePoll } from '@inertiajs/react';
import { ChevronDown, Menu, X } from 'lucide-react';
import type { LucideIcon } from 'lucide-react';
import { Dialog } from 'radix-ui';
import { useEffect, useId, useState } from 'react';
import type { ReactNode } from 'react';
import { IdleTimeout } from '@/components/idle-timeout';
import { MaintenanceGate } from '@/components/maintenance-gate';
import { ErrorBoundary } from '@/components/error-boundary';
import { NetworkStatus } from '@/components/network-status';
import { AccountMenu } from '@/components/account-menu';
import { MfaReminder } from '@/components/mfa-reminder';
import { ReauthProvider } from '@/components/reauth/reauth-provider';
import { NotificationBell } from '@/components/notification-bell';
import { ThemeMenu } from '@/components/theme-menu';
import { ThemedToaster } from '@/components/themed-toaster';
import { Button } from '@/components/ui/button';
import { overlayClass, useRestoreFocus } from '@/components/ui/dialog';
import { TooltipProvider } from '@/components/ui/tooltip';
import { MENU, visibleMenu } from '@/layouts/menu';
import type { MenuLinkItem } from '@/layouts/menu';
import { useBrand } from '@/lib/brand-store';
import { cn } from '@/lib/utils';

type SharedProps = {
    auth: { user: { id: number; name: string; username: string; is_super_admin: boolean } | null };
};

/** The path of the page being shown, without the query string. */
function useCurrentPath(): string {
    const { url } = usePage();

    return url.split('?')[0] ?? url;
}

function isCurrent(path: string, item: MenuLinkItem): boolean {
    return item.end ? path === item.to : path === item.to || path.startsWith(`${item.to}/`);
}

function Brand({ onNavigate, home }: { onNavigate?: () => void; home: string }) {
    const { name, logo } = useBrand();

    return (
        <Link href={home} onClick={onNavigate} className="flex items-center gap-2 text-sm font-semibold">
            <img src={logo} alt="" width={24} height={24} className="size-6" />
            {name}
        </Link>
    );
}

const rowClass =
    'flex h-8 w-full items-center gap-2 rounded-md px-2.5 text-sm font-medium text-muted transition-colors hover:bg-canvas hover:text-ink [&_svg]:size-4';

export function MenuLink({ item, onNavigate }: { item: MenuLinkItem; onNavigate: () => void }) {
    const active = isCurrent(useCurrentPath(), item);

    return (
        <Link
            href={item.to}
            onClick={onNavigate}
            aria-current={active ? 'page' : undefined}
            className={cn(rowClass, !item.icon && 'px-3', active && 'bg-primary-soft text-primary hover:bg-primary-soft hover:text-primary')}
        >
            {item.icon ? <item.icon /> : <span aria-hidden className="size-1 shrink-0 rounded-full bg-current opacity-60" />}
            {item.label}
        </Link>
    );
}

/** A parent entry with its submenu. It is open while one of its pages is shown, until the person closes it; otherwise closed until opened. */
export function SubMenu({ label, icon: Icon, children, onNavigate }: { label: string; icon: LucideIcon; children: MenuLinkItem[]; onNavigate: () => void }) {
    const path = useCurrentPath();
    const id = useId();
    const holdsCurrentPage = children.some((child) => isCurrent(path, child));
    const [toggled, setToggled] = useState<boolean | null>(null);
    const open = toggled ?? holdsCurrentPage;

    return (
        <div className="flex flex-col gap-0.5">
            <button
                type="button"
                aria-expanded={open}
                aria-controls={id}
                onClick={() => setToggled(!open)}
                className={cn(rowClass, 'cursor-pointer', holdsCurrentPage && 'text-ink')}
            >
                <Icon />
                {label}
                <ChevronDown className={cn('ml-auto transition-transform', open && 'rotate-180')} />
            </button>
            {open && (
                <div id={id} className="motion-page ml-[1.125rem] flex flex-col gap-0.5 border-l border-line">
                    {children.map((child) => (
                        <MenuLink key={child.to} item={child} onNavigate={onNavigate} />
                    ))}
                </div>
            )}
        </div>
    );
}

function Sidebar({ onNavigate }: { onNavigate: () => void }) {
    const isSuperAdmin = usePage<SharedProps>().props.auth.user?.is_super_admin ?? false;

    return (
        <nav className="flex flex-col gap-0.5 overflow-y-auto p-2" aria-label="Menu utama">
            {visibleMenu(isSuperAdmin).map((section, index) => (
                <div key={section.label ?? index} className="flex flex-col gap-0.5">
                    {section.label && <p className="mt-2 px-2.5 pb-0.5 text-2xs font-semibold tracking-wide text-muted/80 uppercase">{section.label}</p>}
                    {section.items.map((item) =>
                        'children' in item ? (
                            <SubMenu key={item.label} label={item.label} icon={item.icon} onNavigate={onNavigate}>
                                {item.children}
                            </SubMenu>
                        ) : (
                            <MenuLink key={item.to} item={item} onNavigate={onNavigate} />
                        ),
                    )}
                </div>
            ))}
        </nav>
    );
}

/** The menu drawer of small screens; it is a Radix dialog, so focus and Esc are handled. */
function MobileDrawer({ open, onOpenChange, home }: { open: boolean; onOpenChange: (open: boolean) => void; home: string }) {
    const restoreFocus = useRestoreFocus(open);

    return (
        <Dialog.Root open={open} onOpenChange={onOpenChange}>
            <Dialog.Portal>
                <Dialog.Overlay className={cn(overlayClass, 'z-40 lg:hidden')} />
                <Dialog.Content
                    aria-describedby={undefined}
                    {...restoreFocus}
                    className="motion-slide-left fixed inset-y-0 left-0 z-40 w-64 bg-surface shadow-xl focus:outline-none lg:hidden"
                >
                    <div className="flex h-12 items-center justify-between border-b border-line px-4">
                        <Dialog.Title className="sr-only">Menu navigasi</Dialog.Title>
                        <Brand home={home} onNavigate={() => onOpenChange(false)} />
                        <Dialog.Close asChild>
                            <Button variant="ghost" size="icon" aria-label="Tutup menu">
                                <X />
                            </Button>
                        </Dialog.Close>
                    </div>
                    <Sidebar onNavigate={() => onOpenChange(false)} />
                </Dialog.Content>
            </Dialog.Portal>
        </Dialog.Root>
    );
}

/** The first entry of the menu is the start page, where the brand links to. */
const HOME = MENU[0]?.items[0] && 'to' in MENU[0].items[0] ? MENU[0].items[0].to : '/';

/**
 * The frame of every page of the app: sidebar, top bar, and the providers screens rely on (tooltips, toasts, render errors).
 * A page uses it as its persistent layout: `Page.layout = (page: ReactNode) => <AppLayout>{page}</AppLayout>`.
 */
export default function AppLayout({ children }: { children: ReactNode }) {
    const { auth } = usePage<SharedProps>().props;
    const { url } = usePage();
    usePoll(30_000, { only: ['notifications'] });
    const [mobileOpen, setMobileOpen] = useState(false);

    // The drawer is for small screens: close it when the window grows past the desktop breakpoint, so its scroll lock does not linger.
    useEffect(() => {
        const desktop = window.matchMedia('(min-width: 1024px)');
        const close = () => desktop.matches && setMobileOpen(false);
        desktop.addEventListener('change', close);

        return () => desktop.removeEventListener('change', close);
    }, []);

    return (
        <ErrorBoundary>
            <MaintenanceGate>
                <TooltipProvider delayDuration={200}>
                    <ReauthProvider>
                        <a
                            href="#content"
                            className="sr-only z-50 rounded-md bg-surface px-3 py-2 text-sm font-medium text-primary shadow-md focus:not-sr-only focus:fixed focus:top-2 focus:left-2"
                        >
                            Lewati ke konten
                        </a>
                        <div className="flex min-h-screen">
                            <aside className="sticky top-0 hidden h-screen w-60 shrink-0 flex-col border-r border-line bg-surface lg:flex">
                                <div className="flex h-12 items-center border-b border-line px-4">
                                    <Brand home={HOME} />
                                </div>
                                <Sidebar onNavigate={() => undefined} />
                            </aside>

                            <MobileDrawer open={mobileOpen} onOpenChange={setMobileOpen} home={HOME} />

                            <div className="flex min-w-0 flex-1 flex-col">
                                <header className="sticky top-0 z-30 flex h-12 items-center justify-between border-b border-line bg-surface px-3 sm:px-5">
                                    <Button variant="ghost" size="icon" className="lg:hidden" aria-label="Buka menu" onClick={() => setMobileOpen(true)}>
                                        <Menu />
                                    </Button>
                                    <div className="hidden lg:block" />
                                    {auth.user && (
                                        <div className="flex items-center gap-1">
                                            <ThemeMenu />
                                            <NotificationBell />
                                            <AccountMenu name={auth.user.name} />
                                        </div>
                                    )}
                                </header>
                                {auth.user && <MfaReminder />}
                                <main id="content" tabIndex={-1} className="flex-1 p-3 outline-none sm:p-5">
                                    {/* Keyed by the page, so each page fades in when you move to it. */}
                                    <div key={url} className="motion-page">
                                        {children}
                                    </div>
                                </main>
                            </div>
                        </div>
                        <NetworkStatus />
                        <IdleTimeout />
                        <ThemedToaster />
                    </ReauthProvider>
                </TooltipProvider>
            </MaintenanceGate>
        </ErrorBoundary>
    );
}
