import { useForm } from '@inertiajs/react';
import { Button } from '@/components/ui/button';
import { DialogBody, DialogFooter, Modal } from '@/components/ui/dialog';
import { Field } from '@/components/ui/field';
import { PasswordInput } from '@/components/ui/password-input';
import { store } from '@/routes/password/confirm';

type Props = {
    /** Called after the server accepted the password, to carry on with what the person was about to do. */
    onConfirmed: () => void;
    onClose: () => void;
};

/** Asks for the password again before a sensitive action, so the action is not lost behind a full page. */
export function ReauthDialog({ onConfirmed, onClose }: Props) {
    const form = useForm({ password: '' });

    const submit = () =>
        form.post(store().url, {
            preserveScroll: true,
            preserveState: true,
            only: ['auth', 'errors'],
            onSuccess: onConfirmed,
            onError: () => form.reset('password'),
        });

    return (
        <Modal
            persistent
            open
            onOpenChange={(open) => !open && onClose()}
            title="Konfirmasi Kata Sandi"
            description="Aksi ini sensitif. Masukkan kata sandi Anda untuk melanjutkan."
        >
            <form
                noValidate
                onSubmit={(event) => {
                    event.preventDefault();
                    submit();
                }}
            >
                <DialogBody>
                    <Field label="Kata sandi" error={form.errors.password}>
                        <PasswordInput
                            autoFocus
                            autoComplete="current-password"
                            maxLength={128}
                            aria-invalid={!!form.errors.password}
                            value={form.data.password}
                            onChange={(e) => form.setData('password', e.target.value)}
                        />
                    </Field>
                </DialogBody>
                <DialogFooter>
                    <Button variant="outline" onClick={onClose}>
                        Batal
                    </Button>
                    <Button type="submit" loading={form.processing} disabled={!form.data.password}>
                        Konfirmasi
                    </Button>
                </DialogFooter>
            </form>
        </Modal>
    );
}
