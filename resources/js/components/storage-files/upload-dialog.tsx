import { router } from '@inertiajs/react';
import { toast } from 'sonner';
import { Modal, DialogBody } from '@/components/ui/dialog';
import { FileUploader } from '@/components/ui/file-uploader';
import type { UploadFn } from '@/components/ui/file-uploader';
import { store } from '@/routes/storage/files';

type Props = { storage: string; folder: string; maxMb: number; extensions: string[]; onClose: () => void };

/**
 * Sends one file to the open folder as an Inertia request that does not stop the others (`async`), with its progress, and cancels it
 * when the uploader asks. The list behind the dialog refreshes with the answer of the server.
 */
function uploaderFor(storage: string, folder: string): UploadFn {
    return (file, { onProgress, signal }) =>
        new Promise((resolve, reject) => {
            router.post(
                store(storage).url,
                { path: folder, file },
                {
                    forceFormData: true,
                    async: true,
                    preserveScroll: true,
                    preserveState: true,
                    onProgress: (progress) => onProgress(progress?.percentage ?? 0),
                    onCancelToken: (token) => signal.addEventListener('abort', () => token.cancel(), { once: true }),
                    onSuccess: () => resolve(undefined),
                    onCancel: () => reject(new DOMException('Dibatalkan', 'AbortError')),
                    onError: (errors) => reject(new Error(errors.file ?? errors.storage ?? 'Gagal mengunggah berkas.')),
                },
            );
        });
}

/** No footer: files go up as soon as they are chosen, and the X in the header closes the dialog (what is still going up is cancelled). */
export function UploadDialog({ storage, folder, maxMb, extensions, onClose }: Props) {
    return (
        <Modal open onOpenChange={(next) => !next && onClose()} title="Unggah Berkas" description="Berkas langsung diunggah setelah dipilih.">
            <DialogBody>
                <FileUploader
                    accept={extensions.map((extension) => `.${extension}`).join(',')}
                    description={`Paling besar ${maxMb} MB per berkas · ${extensions.join(', ')}`}
                    maxBytes={maxMb * 1024 * 1024}
                    upload={uploaderFor(storage, folder)}
                    onReject={(message) => toast.error(message)}
                />
            </DialogBody>
        </Modal>
    );
}
