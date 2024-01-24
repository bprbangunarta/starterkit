<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index()
    {
        $keyword = request('keyword');
        if ($keyword) {
            $roles = Role::where('name', 'LIKE', '%' . $keyword . '%')
                ->orWhere('guard_name', 'LIKE', '%' . $keyword . '%')
                ->orderBy('name', 'ASC')
                ->paginate(10);
        } else {
            $roles = Role::orderBy('name', 'ASC')
                ->paginate(10);
        }

        return view('admin.role.index', [
            'roles' => $roles
        ]);
    }

    public function create(Request $request)
    {
        $name = $request->name;

        request()->validate([
            'name' => 'required',
        ]);

        $check = Role::where('name', $name)->count();

        if ($check > 0) {
            return redirect()->back()->with('error', 'Role already exists!');
        }

        $role = Role::create([
            'name'          => request('name'),
            'guard_name'    => request('guard_name') ?? 'web',
        ]);

        if ($role) {
            return redirect()->back()->with('success', 'Created successfully!');
        } else {
            return redirect()->back()->with('error', 'Failed to create role. Please try again.');
        }
    }

    public function update(Request $request)
    {
        $role_id = $request->input('id');
        $role = Role::find($role_id);

        if ($role) {
            $newName = $request->input('name');

            $check = Role::where('name', $newName)->where('id', '!=', $role_id)->count();

            if ($check > 0) {
                return redirect()->back()->with('error', 'Role already exists!');
            }

            $role->update([
                'name'       => $newName,
                'guard_name' => $request->input('guard_name') ?? 'web',
                'updated_at' => now(),
            ]);

            return redirect()->back()->with('success', 'Updated successfully!');
        } else {
            return redirect()->back()->with('error', 'Role not found.');
        }
    }

    public function destroy(Request $request)
    {
        $role_id = $request->input('role_id');
        $role = Role::find($role_id);

        if ($role) {
            $role->delete();
            return redirect()->back()->with('success', 'Deleted successfully!');
        } else {
            return redirect()->back()->with('error', 'Role not found.');
        }
    }
}
