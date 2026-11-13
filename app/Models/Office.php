<?php

namespace App\Models;

use App\Models\Concerns\Audited;
use Database\Factories\OfficeFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property string $name
 * @property string|null $address
 * @property string|null $latitude
 * @property string|null $longitude
 * @property Carbon|null $created_at
 * @property int|null $users_count
 */
class Office extends Model
{
    /** @use HasFactory<OfficeFactory> */
    use Audited, HasFactory;

    /** @var list<string> */
    protected $fillable = ['name', 'address', 'latitude', 'longitude'];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return ['latitude' => 'decimal:7', 'longitude' => 'decimal:7'];
    }

    /** @return HasMany<User, $this> */
    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    /**
     * @param  Builder<Office>  $query
     * @param  array{search?: ?string}  $filters
     * @return Builder<Office>
     */
    public function scopeFiltered(Builder $query, array $filters): Builder
    {
        return $query->when($filters['search'] ?? null, function (Builder $query, string $term): void {
            $pattern = '%'.str_replace(['!', '%', '_'], ['!!', '!%', '!_'], $term).'%';

            $query->where(fn (Builder $match) => $match
                ->whereRaw("name like ? escape '!'", [$pattern])
                ->orWhereRaw("address like ? escape '!'", [$pattern]));
        });
    }

    /**
     * The sort keys are a whitelist; an unknown one sorts by name. Sorting by users needs `withCount('users')` on the query.
     *
     * @param  Builder<Office>  $query
     * @return Builder<Office>
     */
    public function scopeSortedBy(Builder $query, string $sort, string $direction): Builder
    {
        $direction = $direction === 'desc' ? 'desc' : 'asc';

        match ($sort) {
            'address' => $query->orderBy('address', $direction),
            'latitude' => $query->orderBy('latitude', $direction),
            'longitude' => $query->orderBy('longitude', $direction),
            'users' => $query->orderBy('users_count', $direction),
            'created' => $query->orderBy('created_at', $direction),
            default => $query->orderBy('name', $direction),
        };

        return $query->orderBy('id');
    }

    protected function auditLabel(): string
    {
        return 'Kantor';
    }
}
