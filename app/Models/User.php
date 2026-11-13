<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use App\Enums\UserListStatus;
use App\Models\Concerns\Audited;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Carbon;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Traits\HasRoles;

/**
 * @property Carbon|null $password_changed_at
 * @property int|null $office_id
 */
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use Audited, HasFactory, HasRoles, Notifiable, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'password_changed_at' => 'datetime',
        ];
    }

    /** @return BelongsTo<Office, $this> */
    public function office(): BelongsTo
    {
        return $this->belongsTo(Office::class);
    }

    /**
     * @param  Builder<User>  $query
     * @param  array{search?: ?string, role?: ?string, office?: ?string, status?: ?string}  $filters
     * @return Builder<User>
     */
    public function scopeFiltered(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['search'] ?? null, function (Builder $query, string $term): void {
                $pattern = '%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $term).'%';

                $query->where(fn (Builder $match) => $match
                    ->whereRaw("name like ? escape '!'", [$pattern])
                    ->orWhereRaw("username like ? escape '!'", [$pattern])
                    ->orWhereRaw("email like ? escape '!'", [$pattern]));
            })
            ->when($filters['role'] ?? null, fn (Builder $query, string $role) => $query->role($role))
            ->when($filters['office'] ?? null, fn (Builder $query, string $office) => $office === 'none' ? $query->whereNull('office_id') : $query->where('office_id', (int) $office))
            ->tap(fn (Builder $query) => match (UserListStatus::tryFrom((string) ($filters['status'] ?? '')) ?? UserListStatus::Active) {
                UserListStatus::Deleted => $query->onlyTrashed(),
                UserListStatus::All => $query->withTrashed(),
                UserListStatus::Active => $query,
            });
    }

    /**
     * The sort keys are a whitelist; an unknown one sorts by name. Sorting by role uses the first role name through a subquery.
     *
     * @param  Builder<User>  $query
     * @return Builder<User>
     */
    public function scopeSortedBy(Builder $query, string $sort, string $direction): Builder
    {
        $direction = $direction === 'desc' ? 'desc' : 'asc';

        match ($sort) {
            'username' => $query->orderBy('username', $direction),
            'email' => $query->orderBy('email', $direction),
            'office' => $query->orderBy(Office::query()->select('name')->whereColumn('offices.id', 'users.office_id'), $direction),
            'status' => $query->orderBy('deleted_at', $direction),
            'created' => $query->orderBy('created_at', $direction),
            'role' => $query->orderBy(
                Role::query()->selectRaw('min(roles.name)')
                    ->join('model_has_roles', 'model_has_roles.role_id', '=', 'roles.id')
                    ->whereColumn('model_has_roles.model_id', 'users.id')
                    ->where('model_has_roles.model_type', self::class),
                $direction,
            ),
            default => $query->orderBy('name', $direction),
        };

        return $query->orderBy('id');
    }

    /** A password with no change date (new or reset) or older than its lifetime has expired. */
    public function passwordExpired(): bool
    {
        return $this->password_changed_at === null
            || $this->password_changed_at->lt(now()->subDays((int) config('auth.password_max_age_days')));
    }

    /** @return list<string> */
    protected function auditMaskedAttributes(): array
    {
        return ['password'];
    }

    protected function auditLabel(): string
    {
        return 'Pengguna';
    }
}
