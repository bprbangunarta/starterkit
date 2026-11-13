import { Link } from '@inertiajs/react';
import { ShieldAlert } from 'lucide-react';
import { profile } from '@/routes';

// TODO: verifikasi dua langkah ditunda (D-0022); pengingat ini tampil untuk semua akun sampai MFA ada.
export function MfaReminder() {
    return (
        <div role="status" className="flex items-center gap-2 border-b border-warning-line bg-warning-soft px-3 py-1.5 text-xs text-warning-ink sm:px-5">
            <ShieldAlert className="size-3.5 shrink-0" />
            <span className="min-w-0 flex-1 truncate">Akun Anda belum dilindungi verifikasi dua langkah.</span>
            <Link href={profile()} className="shrink-0 font-medium underline underline-offset-2 hover:no-underline">
                Aktifkan
            </Link>
        </div>
    );
}
