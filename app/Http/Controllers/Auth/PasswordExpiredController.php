<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PasswordExpiredController extends Controller
{
    public function show(Request $request): Response|RedirectResponse
    {
        $user = $request->user();

        if (! $user->passwordExpired()) {
            return redirect()->route('dashboard');
        }

        return Inertia::render('password-expired', [
            'name' => $user->name,
            'first_time' => $user->password_changed_at === null,
            'max_age_days' => (int) config('auth.password_max_age_days'),
        ]);
    }
}
