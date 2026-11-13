import { useForm, usePage } from '@inertiajs/react';
import { store } from '@/actions/App/Http/Controllers/Auth/LoginController';
import { AuthLayout } from '@/components/auth-layout';
import { Button } from '@/components/ui/button';
import { Field } from '@/components/ui/field';
import { Input } from '@/components/ui/input';
import { Alert, Card } from '@/components/ui/misc';
import { PasswordInput } from '@/components/ui/password-input';

type SharedProps = { flash: { message: string | null } };

export default function Login() {
    const { flash } = usePage<SharedProps>().props;
    const form = useForm({ login: '', password: '' });

    return (
        <AuthLayout title="Masuk" description="Gunakan email atau username dan kata sandi yang terdaftar." illustration="login">
            {flash.message && (
                <Alert tone="warning" className="mb-3">
                    {flash.message}
                </Alert>
            )}
            <Card className="p-4">
                <form
                    className="flex flex-col gap-3"
                    noValidate
                    onSubmit={(e) => {
                        e.preventDefault();
                        form.post(store().url, { onError: () => form.reset('password') });
                    }}
                >
                    <Field label="Kredensial" error={form.errors.login}>
                        <Input
                            type="text"
                            autoComplete="username"
                            maxLength={255}
                            autoFocus
                            required
                            value={form.data.login}
                            onChange={(e) => form.setData('login', e.target.value)}
                            aria-invalid={!!form.errors.login}
                            placeholder="Email atau Username"
                        />
                    </Field>
                    <Field label="Kata sandi" error={form.errors.password}>
                        <PasswordInput
                            autoComplete="current-password"
                            maxLength={128}
                            required
                            value={form.data.password}
                            onChange={(e) => form.setData('password', e.target.value)}
                            aria-invalid={!!form.errors.password}
                            placeholder="************************"
                        />
                    </Field>
                    <Button type="submit" loading={form.processing} className="w-full" disabled={form.data.login === '' || form.data.password === ''}>
                        Masuk
                    </Button>
                </form>
            </Card>
        </AuthLayout>
    );
}
