import { Head } from '@inertiajs/react';
import type { ReactNode } from 'react';
import { AccountCard } from '@/components/profile/account-card';
import type { Account } from '@/components/profile/account-card';
import { PasswordCard } from '@/components/profile/password-card';
import { TwoFactorCard } from '@/components/profile/two-factor-card';
import { PageHeader } from '@/components/ui/misc';
import AppLayout from '@/layouts/app-layout';

export default function Profile({ account }: { account: Account }) {
    return (
        <>
            <Head title="User Profile" />
            <PageHeader title="User Profile" description="Akun Anda dan cara Anda masuk" />
            <div className="grid gap-3 lg:grid-cols-2">
                <div className="flex flex-col gap-3">
                    <AccountCard account={account} />
                    <PasswordCard />
                </div>
                <TwoFactorCard email={account.email} />
            </div>
        </>
    );
}

Profile.layout = (page: ReactNode) => <AppLayout>{page}</AppLayout>;
