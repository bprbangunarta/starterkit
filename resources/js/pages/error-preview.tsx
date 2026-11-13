import { ErrorPage } from '@/components/error-page';

/** /ui-guideline/errors/503 and the like: an error screen at full size, for the UI Guideline and for checking wording. */
export default function ErrorPreview({ status }: { status: number }) {
    return <ErrorPage status={status} back />;
}
