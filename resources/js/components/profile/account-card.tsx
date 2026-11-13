import type { ReactNode } from 'react';
import { AvatarEditor } from '@/components/avatar-editor';
import { Card, CardBody, CardHeader } from '@/components/ui/misc';

export type Account = { name: string; username: string; email: string };

function Detail({ label, children }: { label: string; children: ReactNode }) {
    return (
        <div className="min-w-0">
            <dt className="text-xs text-muted">{label}</dt>
            <dd className="truncate text-sm font-medium">{children || '–'}</dd>
        </div>
    );
}

export function AccountCard({ account }: { account: Account }) {
    return (
        <Card>
            <CardHeader title="Akun" />
            <CardBody className="flex flex-col gap-3">
                <AvatarEditor name={account.name} src={null} />
                <dl className="grid gap-3 border-t border-line pt-3 sm:grid-cols-2">
                    <Detail label="Nama">{account.name}</Detail>
                    <Detail label="Nama pengguna">{account.username}</Detail>
                    <Detail label="Email">{account.email}</Detail>
                </dl>
            </CardBody>
        </Card>
    );
}
