<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Str;

class UserController extends Controller
{
    public function index()
    {
        $keyword = request('keyword');
        if ($keyword) {
            $users = User::where('name', 'LIKE', '%' . $keyword . '%')
                ->orWhere('username', 'LIKE', '%' . $keyword . '%')
                ->orWhere('email', 'LIKE', '%' . $keyword . '%')
                ->orderBy('name', 'ASC')
                ->paginate(10);
        } else {
            $users = User::orderBy('name', 'ASC')
                ->paginate(10);
        }

        $roles = Role::orderBy('name')->get();

        return view('admin.user.index', [
            'users' => $users,
            'roles' => $roles,
        ]);
    }

    public function create(Request $request)
    {
        $phone = "62" . request('phone');
        request()->validate([
            'name'          => 'required',
            'username'      => 'required',
            'email'         => 'required',
        ]);

        $check = User::where('email', $request->email)
            ->Orwhere('username', $request->username)
            ->Orwhere('phone', $phone)
            ->count();

        if ($check > 0) {
            return redirect()->back()->with('error', 'User already exists!');
        }

        $user = User::create([
            'name'              => request('name'),
            'username'          => request('username'),
            'email'             => request('email'),
            'phone'             => $phone,
            'is_active'         => request('is_active'),
            'email_verified_at' => now(),
            'password'          => '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', // password
            'remember_token'    => Str::random(10),
        ]);

        $user->assignRole(request('role'));

        if ($user) {
            return redirect()->back()->with('success', 'Created successfully!');
        } else {
            return redirect()->back()->with('error', 'Failed to create user. Please try again.');
        }
    }

    // public function update(Request $request)
    // {
    //     $id = $request->input('id');
    //     $phone = "62" . request('phone');

    //     $user = User::find($id);

    //     if ($user) {
    //         $check = User::where('email', $request->email)
    //             ->orWhere('username', $request->username)
    //             ->orWhere('phone', $phone)
    //             ->where('id', '!=', $id)
    //             ->count();

    //         if ($check > 0) {
    //             return redirect()->back()->with('error', 'User already exists!');
    //         }

    //         User::where('id', $user->id)
    //             ->update([
    //                 'name'      => request('name'),
    //                 'username'  => request('username'),
    //                 'email'     => request('email'),
    //                 'phone'     => $phone,
    //                 'is_active' => request('is_active'),
    //             ]);

    //         $user->syncRoles([request('role')]);

    //         return redirect()->back()->with('success', 'Updated successfully!');
    //     } else {
    //         return redirect()->back()->with('error', 'User not found.');
    //     }
    // }

    public function update(Request $request)
    {
        $id = $request->input('id');
        $phone = "62" . request('phone');

        $user = User::find($id);

        if ($user) {
            $duplicateCheck = User::where(function ($query) use ($request, $id, $phone) {
                $query->where('email', $request->email)
                    ->orWhere('username', $request->username)
                    ->orWhere('phone', $phone);
            })
                ->where('id', '!=', $id)
                ->count();

            if ($duplicateCheck > 0) {
                return redirect()->back()->with('error', 'User already exists!');
            }

            $updateData = [
                'name'      => request('name'),
                'username'  => request('username'),
                'email'     => request('email'),
                'phone'     => $phone,
                'is_active' => request('is_active'),
            ];

            User::where('id', $user->id)->update($updateData);

            $user->syncRoles([request('role')]);

            return redirect()->back()->with('success', 'Updated successfully!');
        } else {
            return redirect()->back()->with('error', 'User not found.');
        }
    }


    public function destroy($id)
    {
        $user = User::find($id);

        if (!$user) {
            return response()->json(['error' => 'User not found'], 404);
        }

        $user->delete();
        return response()->json(['success' => true]);
    }
}
