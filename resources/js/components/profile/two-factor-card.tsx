import { KeyRound, Mail, ShieldCheck, Smartphone } from 'lucide-react';
import type { ReactNode } from 'react';
import { Button } from '@/components/ui/button';
import { Badge, Card, CardHeader } from '@/components/ui/misc';

function Method({ icon, action, title, description }: { icon: ReactNode; action: ReactNode; title: string; description: string }) {
    return (
        <div className="flex items-center justify-between gap-3 p-3">
            <div className="flex gap-2.5">
                <span className="mt-0.5 shrink-0 text-muted [&_svg]:size-4">{icon}</span>
                <div>
                    <p className="text-sm font-medium">{title}</p>
                    <p className="text-xs text-muted">{description}</p>
                </div>
            </div>
            <Button size="sm" disabled>
                {action} Atur
            </Button>
        </div>
    );
}

// TODO: verifikasi dua langkah ditunda (D-0022); kartu ini tampil tetapi tombolnya nonaktif sampai MFA dikerjakan.
export function TwoFactorCard({ email }: { email: string }) {
    return (
        <Card className="self-start">
            <CardHeader title="Verifikasi Dua Langkah" actions={<Badge tone="warning">Mati</Badge>} />
            <div className="flex flex-col divide-y divide-line">
                <Method
                    icon={<Smartphone />}
                    action={<ShieldCheck />}
                    title="Aplikasi authenticator"
                    description="Kode dari aplikasi seperti Google Authenticator atau Microsoft Authenticator. Disarankan."
                />
                <Method icon={<Mail />} action={<KeyRound />} title="Kode email" description={`Kode dikirim ke ${email} setiap kali Anda masuk.`} />
            </div>
        </Card>
    );
}
