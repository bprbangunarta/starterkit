import { router } from '@inertiajs/react';
import { ChevronDown, LogOut, UserRound } from 'lucide-react';
import { Avatar } from '@/components/avatar';
import { DropdownContent, DropdownItem, DropdownMenu, DropdownSeparator, DropdownTrigger } from '@/components/ui/dropdown';
import { logout, profile } from '@/routes';

export function AccountMenu({ name }: { name: string }) {
    return (
        <DropdownMenu>
            <DropdownTrigger asChild>
                <button type="button" className="flex cursor-pointer items-center gap-2 rounded-md px-1.5 py-1 hover:bg-canvas">
                    <Avatar name={name} src={null} className="size-6 text-xs" />
                    <span className="hidden text-sm font-medium sm:block">{name}</span>
                    <ChevronDown className="size-3.5 text-muted" />
                </button>
            </DropdownTrigger>
            <DropdownContent>
                <DropdownItem icon={<UserRound />} className="h-8" onSelect={() => router.visit(profile().url)}>
                    Profil
                </DropdownItem>
                <DropdownSeparator />
                <DropdownItem icon={<LogOut />} className="h-8" onSelect={() => router.post(logout().url)}>
                    Keluar
                </DropdownItem>
            </DropdownContent>
        </DropdownMenu>
    );
}
