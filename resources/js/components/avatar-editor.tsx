import { Camera } from 'lucide-react';
import { Avatar } from '@/components/avatar';
import { Button } from '@/components/ui/button';

// TODO: unggah foto profil belum ada (butuh disk privat dan rute yang diotorisasi, auth.md); tombolnya nonaktif.
/** The photo row on the profile page, laid out as in the template. */
export function AvatarEditor({ name, src }: { name: string; src: string | null }) {
    return (
        <div className="flex flex-wrap items-center justify-between gap-2">
            <div className="flex min-w-0 items-center gap-3">
                <Avatar name={name} src={src} className="size-14 text-lg" />
                <div className="min-w-0">
                    <p className="text-sm font-medium">Foto profil</p>
                    <p className="text-xs text-muted">JPG, PNG, atau WebP, maksimal 2 MB.</p>
                </div>
            </div>
            <div className="flex items-center gap-2">
                <Button size="sm" variant="outline" disabled>
                    <Camera /> {src ? 'Ganti foto' : 'Unggah foto'}
                </Button>
            </div>
        </div>
    );
}
