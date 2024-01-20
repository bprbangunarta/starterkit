<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

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
                ->paginate(1);
        }

        $roles = Role::orderBy('name')->get();

        return view('admin.user.index', [
            'users' => $users,
            'roles' => $roles,
        ]);
    }
}
