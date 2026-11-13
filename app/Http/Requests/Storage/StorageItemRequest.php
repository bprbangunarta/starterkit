<?php

namespace App\Http\Requests\Storage;

use App\Rules\StoragePath;
use Illuminate\Foundation\Http\FormRequest;

/** A request about one folder or file: the path of an item, never the top of the storage. */
class StorageItemRequest extends FormRequest
{
    // The route is limited to the Super Admin role (D-0029).
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, list<mixed>> */
    public function rules(): array
    {
        return ['path' => ['required', new StoragePath]];
    }

    public function path(): string
    {
        return (string) $this->validated('path');
    }
}
