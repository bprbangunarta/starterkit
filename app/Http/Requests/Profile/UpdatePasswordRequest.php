<?php

namespace App\Http\Requests\Profile;

use App\Actions\Audit\RecordAudit;
use App\Enums\AuditEvent;
use App\Enums\AuditResult;
use App\Rules\NotRecentlyUsedPassword;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Validator;

class UpdatePasswordRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    /**
     * @return array<string, array<int, mixed>>
     */
    public function rules(): array
    {
        return [
            'current_password' => ['required', 'string', 'current_password'],
            'password' => ['required', 'string', 'confirmed', Password::defaults(), new NotRecentlyUsedPassword($this->user())],
        ];
    }

    /**
     * A wrong current password on a signed-in session is a security event; a too-short new password is just a form error.
     *
     * @return array<int, callable>
     */
    public function after(RecordAudit $recordAudit): array
    {
        return [
            function (Validator $validator) use ($recordAudit): void {
                if (! $validator->errors()->has('current_password')) {
                    return;
                }

                $recordAudit->handle(
                    AuditEvent::PasswordChangeFailed,
                    'Gagal mengganti kata sandi',
                    causer: $this->user(),
                    subject: $this->user(),
                    result: AuditResult::Refused,
                );
            },
        ];
    }
}
