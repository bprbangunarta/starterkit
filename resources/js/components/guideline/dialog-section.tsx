import { useState } from 'react';
import { toast } from 'sonner';
import { Button } from '@/components/ui/button';
import { ConfirmDialog, DialogBody, DialogFooter, Modal } from '@/components/ui/dialog';
import { Drawer, DrawerBody } from '@/components/ui/drawer';
import { Field } from '@/components/ui/field';
import { Input } from '@/components/ui/input';
import { Section } from '@/components/guideline/section';

export function DialogSection() {
    const [modal, setModal] = useState(false);
    const [confirm, setConfirm] = useState(false);
    const [drawer, setDrawer] = useState(false);

    return (
        <>
            <Section
                title="Dialog"
                description="Header (judul), body (isi atau pesan konfirmasi), footer (tombol teks: batal paling kiri, konfirmasi paling kanan). Dialog berisi isian tidak menutup saat klik di luar (persistent); Esc dan tombol tutup tetap bisa; fokus kembali ke tombol pembuka."
            >
                <div className="flex gap-2">
                    <Button variant="outline" onClick={() => setModal(true)}>
                        Buka dialog
                    </Button>
                    <Button variant="outline" onClick={() => setConfirm(true)}>
                        Buka konfirmasi
                    </Button>
                    <Button variant="outline" onClick={() => setDrawer(true)}>
                        Buka laci (Drawer)
                    </Button>
                </div>
            </Section>

            <Modal persistent open={modal} onOpenChange={setModal} title="Contoh Dialog" description="Dialog kecil untuk tambah atau ubah data.">
                <DialogBody>
                    <Field label="Nama" required>
                        <Input autoFocus maxLength={60} />
                    </Field>
                </DialogBody>
                <DialogFooter>
                    <Button variant="outline" onClick={() => setModal(false)}>
                        Batal
                    </Button>
                    <Button
                        onClick={() => {
                            setModal(false);
                            toast.success('Tersimpan');
                        }}
                    >
                        Simpan
                    </Button>
                </DialogFooter>
            </Modal>
            <Drawer open={drawer} onOpenChange={setDrawer} title="Contoh Laci" description="Panel dari samping untuk rincian atau form panjang.">
                <DrawerBody>
                    <p className="text-sm">Isi laci menggulir sendiri; header tetap di atas.</p>
                </DrawerBody>
                <DialogFooter>
                    <Button variant="outline" onClick={() => setDrawer(false)}>
                        Batal
                    </Button>
                    <Button
                        onClick={() => {
                            setDrawer(false);
                            toast.success('Tersimpan');
                        }}
                    >
                        Simpan
                    </Button>
                </DialogFooter>
            </Drawer>
            <ConfirmDialog
                open={confirm}
                onOpenChange={setConfirm}
                title="Hapus Data?"
                description="Ini akan menghapus data secara permanen."
                onConfirm={() => {
                    setConfirm(false);
                    toast.success('Dihapus');
                }}
            />
        </>
    );
}
