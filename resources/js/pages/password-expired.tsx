import { Link, useForm } from '@inertiajs/react';
import { KeyRound } from 'lucide-react';
import { toast } from 'sonner';
import { AuthLayout } from '@/components/auth-layout';
import { EMPTY_PASSWORD, PasswordFields } from '@/components/password-fields';
import type { PasswordData } from '@/components/password-fields';
import { Button, buttonVariants } from '@/components/ui/button';
import { Alert, Card, CardBody, CardFooter } from '@/components/ui/misc';
import { logout } from '@/routes';
import { update } from '@/routes/profile/password';

type Props = { name: string; first_time: boolean; max_age_days: number };

/**
 * Replaces the app while the password is expired, new, or reset by an admin (a case the template lacks); the server refuses a
 * password equal to the current or recent ones.
 */
export default function PasswordExpired({ name, first_time: firstTime, max_age_days: maxAgeDays }: Props) {
    const form = useForm<PasswordData>(EMPTY_PASSWORD);

    return (
        <AuthLayout
            title={firstTime ? 'Buat kata sandi baru' : 'Kata sandi sudah kedaluwarsa'}
            pageTitle={firstTime ? 'Buat kata sandi baru' : 'Kata sandi kedaluwarsa'}
            illustration="password-expired"
            description={
                firstTime
                    ? `Halo, ${name}. Kata sandi Anda dari admin hanya untuk masuk pertama. Buat kata sandi Anda sendiri untuk melanjutkan.`
                    : `Halo, ${name}. Buat kata sandi baru untuk melanjutkan.`
            }
        >
            <Card>
                <form
                    noValidate
                    onSubmit={(e) => {
                        e.preventDefault();
                        form.put(update().url, {
                            onSuccess: () => toast.success('Kata sandi diubah.'),
                            onError: () => form.reset('current_password'),
                        });
                    }}
                >
                    <CardBody className="flex flex-col gap-3">
                        <Alert tone="warning" icon={<KeyRound />} className="text-xs">
                            {firstTime
                                ? 'Kata sandi awal diberikan oleh admin dan harus diganti.'
                                : `Kata sandi diganti setiap ${maxAgeDays} hari. Kata sandi lama Anda sudah melewati batas itu.`}
                        </Alert>
                        <PasswordFields form={form} stacked />
                    </CardBody>
                    <CardFooter>
                        <Link href={logout()} as="button" className={buttonVariants({ variant: 'outline' })}>
                            Keluar
                        </Link>
                        <Button type="submit" loading={form.processing} disabled={!form.data.current_password || !form.data.password}>
                            Simpan dan lanjutkan
                        </Button>
                    </CardFooter>
                </form>
            </Card>
        </AuthLayout>
    );
}
