<?php

namespace App\Http\Requests\Settings;

use App\Enums\SettingKey;
use Illuminate\Foundation\Http\FormRequest;

class StoreBrandImageRequest extends FormRequest
{
    // The route is limited to the Super Admin role (D-0029).
    public function authorize(): bool
    {
        return true;
    }

    /** @return array<string, list<mixed>> */
    public function rules(): array
    {
        $policy = $this->route('kind')->imagePolicy();

        return ['file' => ['required', 'file', 'mimes:'.implode(',', $policy['mimes']), 'max:'.$policy['max_kb']]];
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        /** @var SettingKey $kind */
        $kind = $this->route('kind');

        return ['file' => strtolower($kind->label())];
    }
}
