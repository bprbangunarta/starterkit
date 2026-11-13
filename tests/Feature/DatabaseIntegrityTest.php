<?php

use App\Models\Office;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Role;

uses(RefreshDatabase::class);

/**
 * Columns named `*_id` that are deliberately not foreign keys, each with its reason. Anything else named like that must reference its
 * table, so no row can point at a row that is gone (migrations.md).
 *
 * @var array<string, string>
 */
const NOT_A_FOREIGN_KEY = [
    'activity_log.causer_id' => 'polymorphic: the actor may be of any model, and the history must outlive it',
    'activity_log.subject_id' => 'polymorphic: the subject may be of any model, and the history must outlive it',
    'activity_log.request_id' => 'the id of a web request, not a row',
    'model_has_roles.model_id' => 'polymorphic: the model that holds the role',
    'model_has_permissions.model_id' => 'polymorphic: the model that holds the permission',
    'notifications.notifiable_id' => 'polymorphic: the model that is notified',
    'sessions.user_id' => 'a framework table that is cleaned by the session lifetime',
    'audit_exports.last_id' => 'a snapshot of the newest audit entry at the time of the request, not a reference',
];

/** Every `table.column` of the database, read through the schema builder so it works on MySQL and on sqlite alike. */
function allColumns(): Collection
{
    return collect(Schema::getTables())->flatMap(
        fn (array $table): Collection => collect(Schema::getColumns($table['name']))->map(fn (array $column): string => "{$table['name']}.{$column['name']}"),
    );
}

/** Every `table.column` that is part of a foreign key. */
function foreignKeyColumns(): Collection
{
    return collect(Schema::getTables())->flatMap(
        fn (array $table): Collection => collect(Schema::getForeignKeys($table['name']))
            ->flatMap(fn (array $key): array => $key['columns'])
            ->map(fn (string $column): string => "{$table['name']}.{$column}"),
    );
}

test('every column that references another table is a foreign key, except the ones listed with a reason', function () {
    $idColumns = allColumns()->filter(fn (string $name): bool => str_ends_with($name, '_id'));

    $loose = $idColumns->diff(foreignKeyColumns())->diff(array_keys(NOT_A_FOREIGN_KEY))->values()->all();

    expect($loose)->toBe([], 'columns that look like references but have no foreign key (add one, or list it above with its reason)');
});

test('the exceptions list names only columns that exist', function () {
    expect(collect(array_keys(NOT_A_FOREIGN_KEY))->diff(allColumns())->values()->all())->toBe([]);
});

test('the database itself refuses to delete a role that a user still holds', function () {
    $role = Role::findOrCreate('Dipakai', 'web');
    User::factory()->create()->assignRole($role);

    expect(fn () => DB::table('roles')->where('id', $role->id)->delete())->toThrow(QueryException::class);

    expect(Role::query()->whereKey($role->id)->exists())->toBeTrue()->and(DB::table('model_has_roles')->where('role_id', $role->id)->count())->toBe(1);
});

test('a role with no user can be deleted', function () {
    $role = Role::findOrCreate('Bebas', 'web');

    expect(DB::table('roles')->where('id', $role->id)->delete())->toBe(1);
});

test('the database itself refuses to delete an office that a user still belongs to, deleted users included', function () {
    $office = Office::factory()->create();
    User::factory()->for($office)->create()->delete();

    expect(fn () => DB::table('offices')->where('id', $office->id)->delete())->toThrow(QueryException::class);

    expect(Office::query()->whereKey($office->id)->exists())->toBeTrue();
});

test('an office with no user can be deleted', function () {
    $office = Office::factory()->create();

    expect(DB::table('offices')->where('id', $office->id)->delete())->toBe(1);
});
