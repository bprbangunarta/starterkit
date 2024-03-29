<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Contracts\Auth\StatefulGuard;

class UserController extends Controller
{
    /**
     * The guard implementation.
     *
     * @var \Illuminate\Contracts\Auth\StatefulGuard
     */
    protected $guard;

    /**
     * Create a new controller instance.
     *
     * @param  \Illuminate\Contracts\Auth\StatefulGuard  $guard
     * @return void
     */
    public function __construct(StatefulGuard $guard)
    {
        $this->guard = $guard;
    }

    public function account()
    {
        return view('profile.show');
    }

    public function update_account(Request $request)
    {
        $request->validate([
            'name'      => 'required|string|max:255',
            'username'  => 'required|string|max:255|unique:users,username,' . Auth::user()->id,
            'phone'     => 'required|string|min:10|unique:users,phone,' . Auth::user()->id,
            'email'     => 'required|string|email|max:255|unique:users,email,' . Auth::user()->id,
        ]);

        $data = User::findOrFail(Auth::user()->id);

        $data->name     = $request->input('name');
        $data->username = $request->input('username');
        $data->phone    = "62" . $request->input('phone');
        $data->email    = $request->input('email');

        if ($request->hasFile('profile_photo_path')) {
            $file = $request->file('profile_photo_path');
            $extension = $file->getClientOriginalExtension();
            $imageName = Str::slug(Auth::user()->name) . '.' . $extension;

            $imagePath = $file->storeAs('profile', $imageName);
            $data->profile_photo_path = $imagePath;
        }

        $data->save();

        return redirect()->route('user.account')->with('success', 'User account updated.');
    }

    public function deactivation_account(Request $request)
    {
        $user = User::findOrFail(Auth::user()->id);
        $user->is_active = 0;
        $user->save();

        $this->guard->logout();
        if ($request->hasSession()) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        }

        return redirect()->route('login')->with('success', 'Your account has been deactivated.');
    }

    public function password()
    {
        return view('profile.update-password');
    }
}
