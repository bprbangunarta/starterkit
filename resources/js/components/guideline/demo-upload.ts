import type { UploadFn } from '@/components/ui/file-uploader';

/**
 * A pretend upload for the UI Guideline: it plays progress on a timer and sends nothing. A file name with the word "gagal" is refused,
 * to try the retry button. Real screens pass an `upload` that sends the file to the server.
 */
export const demoUpload: UploadFn = (file, { onProgress, signal }) =>
    new Promise((resolve, reject) => {
        const failing = file.name.toLowerCase().includes('gagal');
        let percent = 0;

        const stop = (error: Error) => {
            window.clearInterval(timer);
            reject(error);
        };

        const timer = window.setInterval(() => {
            percent = Math.min(100, percent + 12);
            onProgress(percent);

            if (failing && percent >= 60) {
                stop(new Error('Server menolak berkas ini (contoh galat).'));
            } else if (percent === 100) {
                window.clearInterval(timer);
                resolve(undefined);
            }
        }, 250);

        signal.addEventListener('abort', () => stop(new DOMException('Dibatalkan', 'AbortError')), { once: true });
    });
