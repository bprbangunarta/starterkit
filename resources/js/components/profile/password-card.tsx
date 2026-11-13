import { useForm } from '@inertiajs/react';
import { toast } from 'sonner';
import { EMPTY_PASSWORD, PasswordFields } from '@/components/password-fields';
import type { PasswordData } from '@/components/password-fields';
import { Button } from '@/components/ui/button';
import { Card, CardBody, CardFooter, CardHeader } from '@/components/ui/misc';
import { update } from '@/routes/profile/password';

export function PasswordCard() {
    const form = useForm<PasswordData>(EMPTY_PASSWORD);

    return (
        <Card>
            <CardHeader title="Kata Sandi" />
            <form
                noValidate
                onSubmit={(e) => {
                    e.preventDefault();
                    form.put(update().url, {
                        preserveScroll: true,
                        onSuccess: () => {
                            form.reset();
                            toast.success('Kata sandi diubah. Perangkat lain keluar dari akun Anda.');
                        },
                        onError: () => form.reset('current_password'),
                    });
                }}
            >
                <CardBody className="flex flex-col gap-3">
                    <PasswordFields form={form} />
                </CardBody>
                <CardFooter>
                    <p className="text-xs text-muted">Perangkat lain akan keluar dari akun Anda.</p>
                    <Button type="submit" loading={form.processing} disabled={!form.data.current_password || !form.data.password}>
                        Ubah kata sandi
                    </Button>
                </CardFooter>
            </form>
        </Card>
    );
}
