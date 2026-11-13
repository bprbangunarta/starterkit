<?php

namespace App\Http\Requests\Roles;

use Closure;
use Illuminate\Foundation\Http\FormRequest;
use Spatie\Permission\Models\Role;

class SaveRoleRequest extends FormRequest
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
        $role = $this->route('role');
        $ignored = $role instanceof Role ? $role->id : null;

        return [
            'name' => [
                'required', 'string', 'max:60', 'regex:/^[\pL\pN][\pL\pN .\-]*$/u',
                // Compared without letter case, so the answer does not depend on the database collation.
                function (string $attribute, mixed $value, Closure $fail) use ($ignored): void {
                    $taken = Role::query()
                        ->where('guard_name', config('auth.defaults.guard'))
                        ->whereRaw('lower(name) = ?', [mb_strtolower((string) $value)])
                        ->when($ignored, fn ($query, int $id) => $query->where('id', '!=', $id))
                        ->exists();

                    if ($taken) {
                        $fail('Nama jabatan sudah dipakai.');
                    }
                },
            ],
        ];
    }
}
