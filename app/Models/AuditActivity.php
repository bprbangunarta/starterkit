<?php

namespace App\Models;

use App\Enums\AuditResult;
use App\Exceptions\AuditLogIsAppendOnly;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\Models\Activity;

/**
 * @property int $id
 * @property string|null $log_name
 * @property string $description
 * @property string|null $event
 * @property AuditResult $result
 * @property string|null $request_id
 * @property string|null $ip_address
 * @property string|null $method
 * @property string|null $path
 * @property string|null $user_agent
 * @property Carbon $created_at
 */
class AuditActivity extends Activity
{
    /** Retention deletes through the query builder, which does not fire these model events. */
    protected static function booted(): void
    {
        static::updating(function (): never {
            throw new AuditLogIsAppendOnly;
        });

        static::deleting(function (): never {
            throw new AuditLogIsAppendOnly;
        });
    }

    protected function casts(): array
    {
        return [...parent::casts(), 'result' => AuditResult::class];
    }

    /**
     * @param  Builder<AuditActivity>  $query
     * @param  array{search?: ?string, module?: ?string, result?: ?string, from?: ?string, to?: ?string}  $filters
     * @return Builder<AuditActivity>
     */
    public function scopeFiltered(Builder $query, array $filters): Builder
    {
        return $query
            ->when($filters['search'] ?? null, fn (Builder $query, string $term) => $query->where(fn (Builder $query) => self::matchingSearch($query, $term)))
            ->when($filters['module'] ?? null, fn (Builder $query, string $module) => $query->where('log_name', $module))
            ->when($filters['result'] ?? null, fn (Builder $query, string $result) => $query->where('result', $result))
            ->when($filters['from'] ?? null, fn (Builder $query, string $from) => $query->where('created_at', '>=', Carbon::parse($from)->startOfDay()))
            ->when($filters['to'] ?? null, fn (Builder $query, string $to) => $query->where('created_at', '<=', Carbon::parse($to)->endOfDay()));
    }

    /**
     * The sort keys are a whitelist; an unknown one sorts by time. Sorting by user uses the actor's name through a subquery
     * that no index covers, which is fine for a page of a few hundred thousand entries and worth revisiting beyond that.
     *
     * @param  Builder<AuditActivity>  $query
     * @return Builder<AuditActivity>
     */
    public function scopeSortedBy(Builder $query, string $sort, string $direction): Builder
    {
        $direction = $direction === 'asc' ? 'asc' : 'desc';

        match ($sort) {
            'host' => $query->orderBy('ip_address', $direction),
            'user' => $query->orderBy(
                User::query()->withTrashed()->select('name')->whereColumn('users.id', 'activity_log.causer_id')->whereRaw('activity_log.causer_type = ?', [User::class])->limit(1),
                $direction,
            ),
            'module' => $query->orderBy('log_name', $direction),
            'event' => $query->orderBy('event', $direction),
            'record' => $query->orderBy('subject_type', $direction)->orderBy('subject_id', $direction),
            'outcome' => $query->orderBy('result', $direction),
            default => $query->orderBy('created_at', $direction),
        };

        return $query->orderByDesc('id');
    }

    /** The search matches the description, module, IP address, and the actor's name or username, with % and _ taken literally. */
    private static function matchingSearch(Builder $query, string $term): void
    {
        $pattern = '%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $term).'%';

        $query->whereRaw("description like ? escape '!'", [$pattern])
            ->orWhereRaw("log_name like ? escape '!'", [$pattern])
            ->orWhereRaw("ip_address like ? escape '!'", [$pattern])
            ->orWhereHasMorph('causer', [User::class], function (Builder $causer) use ($pattern): void {
                /** @var Builder<User> $causer */
                $causer->withTrashed()->where(fn (Builder $name) => $name->whereRaw("name like ? escape '!'", [$pattern])->orWhereRaw("username like ? escape '!'", [$pattern]));
            });
    }
}
