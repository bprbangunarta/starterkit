<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Modul;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class PermissionController extends Controller
{
    public function index()
    {
        $keyword = request('keyword');
        if ($keyword) {
            $permissions = Permission::where('name', 'LIKE', '%' . $keyword . '%')
                ->orWhere('guard_name', 'LIKE', '%' . $keyword . '%')
                ->orderBy('name', 'ASC')
                ->paginate(10);
        } else {
            $permissions = Permission::orderBy('name', 'ASC')
                ->paginate(10);
        }

        return view('admin.permission.index', [
            'permissions' => $permissions,
        ]);
    }

    public function create(Request $request)
    {
        $name = $request->name;

        request()->validate([
            'name' => 'required',
        ]);

        $check = Permission::where('name', $name)->count();

        if ($check > 0) {
            return redirect()->back()->with('error', 'Permission already exists!');
        }

        $permission = Permission::create([
            'name'          => request('name'),
            'guard_name'    => request('guard_name') ?? 'web',
        ]);

        if ($permission) {
            return redirect()->back()->with('success', 'Created successfully!');
        } else {
            return redirect()->back()->with('error', 'Failed to create permission. Please try again.');
        }
    }

    public function update(Request $request)
    {
        $id = $request->input('id');
        $permission = Permission::find($id);

        if ($permission) {
            $newName = $request->input('name');

            $check = Permission::where('name', $newName)->where('id', '!=', $id)->count();

            if ($check > 0) {
                return redirect()->back()->with('error', 'Permission already exists!');
            }

            $permission->update([
                'name'       => $newName,
                'guard_name' => $request->input('guard_name') ?? 'web',
                'updated_at' => now(),
            ]);

            return redirect()->back()->with('success', 'Updated successfully!');
        } else {
            return redirect()->back()->with('error', 'Permission not found.');
        }
    }

    public function destroy(Request $request)
    {
        $id = $request->input('permission_id');
        $permission = Permission::find($id);

        if ($permission) {
            $permission->delete();
            return redirect()->back()->with('success', 'Deleted successfully!');
        } else {
            return redirect()->back()->with('error', 'Permission not found.');
        }
    }

    public function assign()
    {
        try {
            request()->validate([
                'role' => 'required',
                'permissions' => 'array|required',
            ]);

            $role = Role::find(request('role'));

            if (!$role) {
                throw new \Exception('Role not found.');
            }

            $role->givePermissionTo(request('permissions'));

            return back()->with('success', 'Assign Permissions successfully!');
        } catch (\Exception $e) {
            return back()->with('error', 'Permissions already Assign.');
        }
    }

    public function sync_edit(Role $role)
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

        $permissions = Permission::orderBy('name', 'ASC')
            ->paginate(10);

        return view('admin.permission.sync', [
            'role'        => $role,
            'roles'       => $roles,
            'permissions' => $permissions,
        ]);
    }

    public function sync_update(Role $role)
    {
        try {
            request()->validate([
                'role' => 'required',
                'permissions' => 'array|required',
            ]);

            if (!$role) {
                throw new \Exception('Role not found.');
            }

            $role->syncPermissions(request('permissions'));
            return redirect('admin/role')->with('success', 'Sync Permissions successfully!');
        } catch (\Exception $e) {
            return redirect('admin/role')->with('error', 'Failed to Sync Permissions.');
        }
    }
}
