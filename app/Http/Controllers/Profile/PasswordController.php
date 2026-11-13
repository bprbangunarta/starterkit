<?php

namespace App\Http\Controllers\Profile;

use App\Actions\Auth\SetPassword;
use App\Http\Controllers\Controller;
use App\Http\Requests\Profile\UpdatePasswordRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class PasswordController extends Controller
{
    public function update(UpdatePasswordRequest $request, SetPassword $setPassword): RedirectResponse
    {
        $password = $request->validated('password');
        $user = $request->user();

        $setPassword->change($user, $password);

        // Ending the other sessions re-saves the same password, which would log a second identical entry.
        $user->disableLogging();

        try {
            Auth::logoutOtherDevices($password);
        } finally {
            $user->enableLogging();
        }

        $request->session()->regenerate();

        return back();
    }
}
