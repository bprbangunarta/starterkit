<?php

namespace App\Http\Controllers\Users;

use App\Actions\Users\RestoreUser;
use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

class RestoredUserController extends Controller
{
    public function store(User $user, RestoreUser $restore): RedirectResponse
    {
        $restore->handle($user);

        return back();
    }
}
