import type { InertiaFormProps } from '@inertiajs/react';
import { Field } from '@/components/ui/field';
import { COORDINATE_CHARACTER, FilteredInput } from '@/components/ui/filtered-input';
import { Input } from '@/components/ui/input';
import { Textarea } from '@/components/ui/textarea';

export type OfficeData = { name: string; address: string; latitude: string; longitude: string };

/** The fields of the office form. The coordinates are decimal degrees, typed as text and checked again by the server. */
export function OfficeFields({ form }: { form: InertiaFormProps<OfficeData> }) {
    return (
        <>
            <Field label="Nama" required error={form.errors.name} className="sm:col-span-2">
                <Input
                    autoFocus
                    maxLength={100}
                    value={form.data.name}
                    onChange={(e) => form.setData('name', e.target.value)}
                    aria-invalid={!!form.errors.name}
                />
            </Field>
            <Field label="Alamat" error={form.errors.address} className="sm:col-span-2">
                <Textarea
                    maxLength={255}
                    value={form.data.address}
                    onChange={(e) => form.setData('address', e.target.value)}
                    aria-invalid={!!form.errors.address}
                />
            </Field>
            <Field
                label="Latitude"
                error={form.errors.latitude}
                hint="Derajat desimal, -90 sampai 90, paling banyak 7 angka di belakang titik. Isi bersama longitude atau kosongkan keduanya"
            >
                <FilteredInput
                    allowed={COORDINATE_CHARACTER}
                    inputMode="decimal"
                    maxLength={11}
                    value={form.data.latitude}
                    onValueChange={(value) => form.setData('latitude', value)}
                    aria-invalid={!!form.errors.latitude}
                />
            </Field>
            <Field
                label="Longitude"
                error={form.errors.longitude}
                hint="Derajat desimal, -180 sampai 180, paling banyak 7 angka di belakang titik. Isi bersama latitude atau kosongkan keduanya"
            >
                <FilteredInput
                    allowed={COORDINATE_CHARACTER}
                    inputMode="decimal"
                    maxLength={12}
                    value={form.data.longitude}
                    onValueChange={(value) => form.setData('longitude', value)}
                    aria-invalid={!!form.errors.longitude}
                />
            </Field>
        </>
    );
}
