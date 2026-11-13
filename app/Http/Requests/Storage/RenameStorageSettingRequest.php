<?php

namespace App\Http\Requests\Storage;

use App\Enums\StorageType;
use App\Models\StorageSetting;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RenameStorageSettingRequest extends FormRequest
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
        $type = $this->route('type');
        $ignored = $type instanceof StorageType ? StorageSetting::query()->where('type', $type->value)->value('id') : null;

        return ['name' => ['nullable', 'string', 'max:100', Rule::unique('storage_settings', 'name')->ignore($ignored)]];
    }

    /** @return array<string, string> */
    public function messages(): array
    {
        return ['name.unique' => 'Nama ini sudah dipakai penyimpanan lain.'];
    }
}
