<?php

namespace App\Http\Controllers\Users;

use App\Actions\Users\ResetUserPassword;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class UserPasswordResetController extends Controller
{
    public function store(Request $request, User $user, ResetUserPassword $reset): RedirectResponse
    {
        $reset->handle($user, $request->user());

        return back();
    }
}
