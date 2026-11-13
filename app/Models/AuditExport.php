<?php

namespace App\Models;

use App\Enums\AuditExportStatus;
use App\Models\Concerns\Audited;
use Database\Factories\AuditExportFactory;
use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Carbon;
use Spatie\Activitylog\Support\LogOptions;

/**
 * One request to export the audit log: the filters it was made with, and the file once the queue has written it.
 *
 * @property string $id
 * @property int $user_id
 * @property AuditExportStatus $status
 * @property array{search?: ?string, module?: ?string, result?: ?string, from?: ?string, to?: ?string} $filters
 * @property int $last_id
 * @property int $rows
 * @property string|null $path
 * @property Carbon|null $ready_at
 * @property Carbon $expires_at
 */
class AuditExport extends Model
{
    /** @use HasFactory<AuditExportFactory> */
    use Audited, HasFactory, HasUlids;

    /** @var list<string> */
    protected $fillable = ['user_id', 'status', 'filters', 'last_id', 'rows', 'path', 'ready_at', 'expires_at'];

    protected function casts(): array
    {
        return [
            'status' => AuditExportStatus::class,
            'filters' => 'array',
            'ready_at' => 'datetime',
            'expires_at' => 'datetime',
        ];
    }

    /** The `exported` event is this record's audit entry, so the status changes of the row itself stay out of the log. */
    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()->logOnly([])->dontLogEmptyChanges();
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class)->withTrashed();
    }
}
