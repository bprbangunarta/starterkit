import type { Draft } from '@/components/guideline/crud-data';
import { TONES } from '@/components/guideline/guideline-data';
import { Button } from '@/components/ui/button';
import { Combobox } from '@/components/ui/combobox';
import { DialogBody, DialogFooter, Modal } from '@/components/ui/dialog';
import { Field } from '@/components/ui/field';
import { Input } from '@/components/ui/input';
import type { BadgeTone } from '@/components/ui/misc';

type Props = {
    draft: Draft | null;
    nameError: string | undefined;
    onChange: (draft: Draft) => void;
    onSubmit: () => void;
    onClose: () => void;
};

/** Persistent, so typed input is not lost on an outside click. */
export function RowFormDialog({ draft, nameError, onChange, onSubmit, onClose }: Props) {
    return (
        <Modal
            persistent
            open={draft !== null}
            onOpenChange={(next) => !next && onClose()}
            title={draft?.id === null ? 'Tambah Data' : 'Ubah Data'}
            description="Isi lalu simpan. Galat validasi tampil di bawah kolom."
        >
            {draft && (
                <form
                    noValidate
                    onSubmit={(e) => {
                        e.preventDefault();
                        onSubmit();
                    }}
                >
                    <DialogBody className="grid gap-3">
                        <Field label="Nama" required error={nameError}>
                            <Input
                                autoFocus
                                maxLength={60}
                                value={draft.name}
                                onChange={(e) => onChange({ ...draft, name: e.target.value })}
                                aria-invalid={!!nameError}
                            />
                        </Field>
                        <Field label="Status">
                            <Combobox
                                searchable={false}
                                options={TONES.map((t) => ({ value: t, label: t }))}
                                value={draft.status}
                                onChange={(v) => onChange({ ...draft, status: (v ?? 'neutral') as BadgeTone })}
                            />
                        </Field>
                        <Field label="Nilai (Rp)">
                            <Input
                                inputMode="numeric"
                                value={draft.amount}
                                onChange={(e) => onChange({ ...draft, amount: e.target.value.replace(/\D/g, '') })}
                            />
                        </Field>
                    </DialogBody>
                    <DialogFooter>
                        <Button variant="outline" onClick={onClose}>
                            Batal
                        </Button>
                        <Button type="submit">Simpan</Button>
                    </DialogFooter>
                </form>
            )}
        </Modal>
    );
}
