import { router, usePage } from '@inertiajs/react';
import { useCallback } from 'react';
import { toast } from 'sonner';
import { Button } from '@/components/ui/button';
import { DialogBody, DialogFooter, Modal } from '@/components/ui/dialog';
import { useIdleCountdown } from '@/hooks/use-idle-countdown';
import { logout } from '@/routes';

type Shared = { session: { idle_minutes: number } };

/**
 * Signs the person out after the idle time the server allows (`session.idle_minutes`, 0 for a guest). The server ends the session too;
 * this keeps the screen in step: a dialog offers to stay signed in one minute before the end, and at the end the person is signed out.
 */
export function IdleTimeout() {
    const idleMinutes = usePage<Shared>().props.session?.idle_minutes ?? 0;

    const leave = useCallback(() => {
        router.post(logout().url, {}, { onFinish: () => toast.info('Sesi berakhir karena tidak ada aktivitas. Silakan masuk lagi.') });
    }, []);

    const { secondsLeft, reset } = useIdleCountdown(idleMinutes * 60_000, leave);

    // A request keeps the server's clock running; if the session had already ended, the answer sends the person to the sign-in page.
    const stay = () => {
        reset();
        router.reload({ only: ['auth'] });
    };

    return (
        <Modal open={secondsLeft !== null} onOpenChange={() => undefined} title="Sesi Akan Berakhir" description="Tidak ada aktivitas." persistent>
            <DialogBody>
                <p className="text-sm">
                    Demi keamanan, Anda akan keluar otomatis dalam <strong>{secondsLeft ?? 0} detik</strong>. Pilih &ldquo;Tetap masuk&rdquo; untuk melanjutkan.
                </p>
            </DialogBody>
            <DialogFooter>
                <Button variant="outline" onClick={leave}>
                    Keluar
                </Button>
                <Button onClick={stay}>Tetap masuk</Button>
            </DialogFooter>
        </Modal>
    );
}
