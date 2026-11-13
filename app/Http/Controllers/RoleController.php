<?php

namespace App\Http\Controllers;

use App\Actions\Roles\DeleteRole;
use App\Actions\Roles\SaveRole;
use App\Http\Requests\Roles\RoleIndexRequest;
use App\Http\Requests\Roles\SaveRoleRequest;
use App\Http\Resources\RoleResource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class RoleController extends Controller
{
    public function index(RoleIndexRequest $request): Response
    {
        $filters = $request->filters();

        $roles = Role::query()
            ->withCount('users')
            ->when($filters['search'], fn (Builder $query, string $term) => $query->whereRaw("name like ? escape '!'", ['%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $term).'%']))
            ->orderBy(match ($filters['sort']) {
                'users' => 'users_count',
                'created' => 'created_at',
                default => 'name',
            }, $filters['direction'])
            ->orderBy('id')
            ->paginate($filters['per_page'])
            ->withQueryString();

        return Inertia::render('roles', ['roles' => RoleResource::collection($roles), 'filters' => $filters]);
    }

    public function store(SaveRoleRequest $request, SaveRole $save): RedirectResponse
    {
        $save->handle(null, $request->validated('name'), $request->user());

        return back();
    }

    public function update(SaveRoleRequest $request, Role $role, SaveRole $save): RedirectResponse
    {
        $save->handle($role, $request->validated('name'), $request->user());

        return back();
    }

    public function destroy(Request $request, Role $role, DeleteRole $delete): RedirectResponse
    {
        $delete->handle($role, $request->user());

        return back();
    }
}
