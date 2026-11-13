<?php

namespace App\Http\Requests\Storage;

use App\Rules\StorageItemName;
use App\Rules\StoragePath;
use Illuminate\Foundation\Http\FormRequest;

class RenameStorageFileRequest extends FormRequest
{
    // The route is limited to the Super Admin role (D-0029).
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('name'))) {
            $this->merge(['name' => trim($this->input('name'))]);
        }
    }

    /** @return array<string, list<mixed>> */
    public function rules(): array
    {
        return [
            'path' => ['required', new StoragePath],
            'name' => ['required', 'string', new StorageItemName],
        ];
    }
}
