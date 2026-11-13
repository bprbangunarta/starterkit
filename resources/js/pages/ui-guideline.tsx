import { Head } from '@inertiajs/react';
import type { ReactNode } from 'react';
import { toast } from 'sonner';
import {
    BadgeAndStateSection,
    ButtonSection,
    CardSection,
    DropdownSection,
    IllustrationSection,
    SidebarMenuSection,
    TabSection,
} from '@/components/guideline/basic-sections';
import { EmailSection, ErrorPagesSection } from '@/components/guideline/error-sections';
import { NavigationSection } from '@/components/guideline/navigation-section';
import { ThemeSection } from '@/components/guideline/theme-section';
import { CrudExample } from '@/components/guideline/crud-example';
import { DialogSection } from '@/components/guideline/dialog-section';
import { ChartSection, ColorSection, StatusColorSection, TypographySection } from '@/components/guideline/foundation-sections';
import { InputExamples } from '@/components/guideline/input-examples';
import { Button } from '@/components/ui/button';
import { PageHeader } from '@/components/ui/misc';
import AppLayout from '@/layouts/app-layout';

/** A living reference of every shared component and token. Compare any new screen against it. */
export default function UiGuideline() {
    return (
        <>
            <Head title="UI Guideline" />
            <PageHeader
                title="UI Guideline"
                description="Acuan komponen dan token. Tampilan baru harus terlihat sama dengan ini"
                actions={
                    <Button variant="outline" onClick={() => toast.success('Contoh toast sukses')}>
                        Coba toast
                    </Button>
                }
            />

            <div className="flex flex-col gap-3">
                <ColorSection />
                <TypographySection />
                <ChartSection />
                <ThemeSection />
                <ButtonSection />
                <InputExamples />
                <StatusColorSection />
                <BadgeAndStateSection />
                <CrudExample />
                <CardSection />
                <DropdownSection />
                <NavigationSection />
                <SidebarMenuSection />
                <DialogSection />
                <IllustrationSection />
                <ErrorPagesSection />
                <EmailSection />
                <TabSection />
            </div>
        </>
    );
}

UiGuideline.layout = (page: ReactNode) => <AppLayout>{page}</AppLayout>;
