import '@fontsource-variable/instrument-sans';
import { createInertiaApp, router } from '@inertiajs/react';
import { APP_LOGO, APP_NAME } from '@/lib/brand';
import { setBrand } from '@/lib/brand-store';
import { listenForErrors, reloadOnStaleChunk } from '@/lib/report';

listenForErrors();
reloadOnStaleChunk();

type SharedBrand = { name?: string; logo?: string | null };

/** The name and logo of the app come with every page (`brand` in the shared props), from the project settings. */
const brandOf = (props: Record<string, unknown>): SharedBrand => (props.brand as SharedBrand | undefined) ?? {};
const nameOf = (props: Record<string, unknown>): string => brandOf(props).name ?? APP_NAME;

router.on('navigate', (event) => setBrand({ name: nameOf(event.detail.page.props), logo: brandOf(event.detail.page.props).logo ?? APP_LOGO }));

void createInertiaApp({
    title: (title, page) => (title ? `${title} - ${nameOf(page.props)}` : nameOf(page.props)),
});
