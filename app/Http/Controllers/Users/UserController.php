<?php

namespace App\Http\Controllers\Users;

use App\Actions\Users\DeleteUser;
use App\Actions\Users\SaveUser;
use App\Http\Controllers\Controller;
use App\Http\Requests\Users\SaveUserRequest;
use App\Http\Requests\Users\UserIndexRequest;
use App\Http\Resources\UserResource;
use App\Models\Office;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    public function index(UserIndexRequest $request): Response
    {
        $filters = $request->filters();

        $users = User::query()
            ->with(['roles:id,name', 'office:id,name'])
            ->filtered($filters)
            ->sortedBy($filters['sort'], $filters['direction'])
            ->paginate($filters['per_page'])
            ->withQueryString();

        return Inertia::render('users', [
            'users' => UserResource::collection($users),
            'filters' => $filters,
            'roles' => Role::query()->orderBy('name')->pluck('name')->all(),
            'offices' => Office::query()->orderBy('name')->get(['id', 'name'])->map(fn (Office $office): array => ['id' => $office->id, 'name' => $office->name])->all(),
        ]);
    }

    public function store(SaveUserRequest $request, SaveUser $save): RedirectResponse
    {
        $save->handle(null, $request->safe()->only(['name', 'username', 'email', 'office_id']), $request->validated('roles', []), $request->user(), $request->validated('reason'));

        return back();
    }

    public function update(SaveUserRequest $request, User $user, SaveUser $save): RedirectResponse
    {
        $save->handle($user, $request->safe()->only(['name', 'username', 'email', 'office_id']), $request->validated('roles', []), $request->user(), $request->validated('reason'));

        return back();
    }

    public function destroy(Request $request, User $user, DeleteUser $delete): RedirectResponse
    {
        $delete->handle($user, $request->user());

        return back();
    }
}
