<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Audit\RecordAudit;
use App\Enums\AuditEvent;
use App\Enums\AuditResult;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class ConfirmPasswordController extends Controller
{
    /** Remembers for `auth.password_timeout` seconds that the person proved their password again; a wrong one is recorded. */
    public function store(Request $request, RecordAudit $recordAudit): RedirectResponse
    {
        $request->validate(['password' => ['required', 'string', 'max:128']]);

        if (! Hash::check($request->string('password')->toString(), $request->user()->password)) {
            $recordAudit->handle(
                AuditEvent::PasswordConfirmFailed,
                'Gagal mengonfirmasi kata sandi',
                causer: $request->user(),
                subject: $request->user(),
                result: AuditResult::Refused,
            );

            throw ValidationException::withMessages(['password' => 'Kata sandi salah.']);
        }

        $request->session()->passwordConfirmed();

        return back();
    }
}
