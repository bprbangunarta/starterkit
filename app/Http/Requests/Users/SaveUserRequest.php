<?php

namespace App\Http\Requests\Users;

use App\Models\User;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class SaveUserRequest extends FormRequest
{
    // The route is limited to the Super Admin role (D-0029).
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('email'))) {
            $this->merge(['email' => mb_strtolower(trim($this->input('email')))]);
        }
    }

    /**
     * The username may not contain "@": the sign-in field treats text with "@" as an email address. The password is never typed
     * here: a new account gets the default one and a reset is its own action.
     *
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        $user = $this->route('user');
        $ignored = $user instanceof User ? $user->id : null;

        return [
            'name' => ['required', 'string', 'max:100'],
            'username' => ['required', 'string', 'max:50', 'regex:/^[A-Za-z0-9._-]+$/', Rule::unique('users', 'username')->ignore($ignored)],
            'email' => ['required', 'string', 'email', 'max:100', Rule::unique('users', 'email')->ignore($ignored)],
            'office_id' => ['nullable', 'integer', Rule::exists('offices', 'id')],
            'password' => ['prohibited'],
            'roles' => ['array', 'max:20'],
            'reason' => ['nullable', 'string', 'max:200'],
            'roles.*' => ['string', 'distinct', Rule::exists('roles', 'name')->where('guard_name', config('auth.defaults.guard'))],
        ];
    }

    /** A change of roles is a sensitive change, so it needs a reason, which goes into the audit entry. */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($this->rolesChange() && trim((string) $this->input('reason')) === '') {
                    $validator->errors()->add('reason', 'Isi alasan perubahan jabatan.');
                }
            },
        ];
    }

    private function rolesChange(): bool
    {
        $user = $this->route('user');
        $current = $user instanceof User ? $user->roles()->pluck('name')->sort()->values()->all() : [];
        $submitted = collect((array) $this->input('roles', []))->unique()->sort()->values()->all();

        return $current !== $submitted;
    }
}
