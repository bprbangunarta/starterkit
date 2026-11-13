<?php

namespace App\Http\Requests\Storage;

use App\Rules\StoragePath;
use Illuminate\Foundation\Http\FormRequest;

class UploadStorageFileRequest extends FormRequest
{
    // The route is limited to the Super Admin role (D-0029).
    public function authorize(): bool
    {
        return true;
    }

    /**
     * The size limit and the allowed extensions come from `config/storage_connections.php`. The server's own limits (`upload_max_filesize`
     * and `post_max_size` of PHP, and the web server's body limit) must be at least as large, or a big file never reaches this check.
     *
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return [
            'path' => ['nullable', new StoragePath],
            'file' => [
                'required',
                'file',
                'max:'.(int) config('storage_connections.upload_max_kb'),
                'extensions:'.implode(',', (array) config('storage_connections.upload_extensions')),
            ],
        ];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return [
            'file.max' => 'Ukuran berkas paling besar '.((int) config('storage_connections.upload_max_kb') / 1024).' MB.',
            'file.extensions' => 'Jenis berkas ini tidak diizinkan.',
        ];
    }
}
