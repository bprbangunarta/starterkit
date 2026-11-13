import type { InertiaFormProps } from '@inertiajs/react';
import type { OfficeOption } from '@/components/offices/types';
import { Combobox } from '@/components/ui/combobox';
import { Field } from '@/components/ui/field';
import { Input } from '@/components/ui/input';
import { MultiCombobox } from '@/components/ui/multi-combobox';

export type UserData = { name: string; username: string; email: string; office_id: string | null; roles: string[]; reason: string };

const sameRoles = (a: string[], b: string[]) => [...a].sort().join('\n') === [...b].sort().join('\n');

type Props = { form: InertiaFormProps<UserData>; roles: string[]; offices: OfficeOption[]; original: string[] };

/** The fields of the user form. A change of roles asks for a reason, which goes into the audit entry. */
export function UserFields({ form, roles, offices, original }: Props) {
    return (
        <>
            <Field label="Nama" required error={form.errors.name}>
                <Input maxLength={100} value={form.data.name} onChange={(e) => form.setData('name', e.target.value)} aria-invalid={!!form.errors.name} />
            </Field>
            <Field label="Username" required error={form.errors.username} hint="Huruf, angka, titik, garis bawah, dan strip; tanpa @ dan spasi">
                <Input
                    maxLength={50}
                    autoComplete="off"
                    value={form.data.username}
                    onChange={(e) => form.setData('username', e.target.value)}
                    aria-invalid={!!form.errors.username}
                />
            </Field>
            <Field label="Email" required error={form.errors.email}>
                <Input
                    maxLength={100}
                    autoComplete="off"
                    value={form.data.email}
                    onChange={(e) => form.setData('email', e.target.value)}
                    aria-invalid={!!form.errors.email}
                />
            </Field>
            <Field label="Kantor" error={form.errors.office_id}>
                <Combobox
                    clearable
                    options={offices.map((office) => ({ value: String(office.id), label: office.name }))}
                    value={form.data.office_id}
                    onChange={(office) => form.setData('office_id', office)}
                    placeholder={offices.length === 0 ? 'Belum ada kantor. Tambahkan di menu Data Kantor' : 'Tanpa kantor'}
                />
            </Field>
            <Field label="Jabatan" error={form.errors.roles}>
                <MultiCombobox
                    options={roles.map((role) => ({ value: role, label: role }))}
                    value={form.data.roles}
                    onChange={(selected) => form.setData('roles', selected)}
                    placeholder={roles.length === 0 ? 'Belum ada jabatan. Tambahkan di menu Data Jabatan' : 'Pilih jabatan…'}
                />
            </Field>
            {!sameRoles(form.data.roles, original) && (
                <Field
                    label={original.length === 0 ? 'Alasan pemberian jabatan' : 'Alasan perubahan jabatan'}
                    required
                    error={form.errors.reason}
                    className="sm:col-span-2"
                    hint="Dicatat di audit logs"
                >
                    <Input maxLength={200} value={form.data.reason} onChange={(e) => form.setData('reason', e.target.value)} />
                </Field>
            )}
        </>
    );
}
