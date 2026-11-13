<?php

namespace App\Http\Requests\Settings;

use App\Enums\SettingKey;
use App\Rules\UsableStorage;
use Illuminate\Foundation\Http\FormRequest;

class UpdateSettingsRequest extends FormRequest
{
    // The route is limited to the Super Admin role (D-0029).
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, list<mixed>> */
    public function rules(): array
    {
        return [
            SettingKey::AppName->value => ['required', 'string', 'max:60'],
            SettingKey::MetaDescription->value => ['nullable', 'string', 'max:160'],
            SettingKey::ActiveStorage->value => ['required', 'string', new UsableStorage],
        ];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return collect(SettingKey::cases())->mapWithKeys(fn (SettingKey $key): array => [$key->value => strtolower($key->label())])->all();
    }
}
