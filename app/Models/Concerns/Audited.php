<?php

namespace App\Models\Concerns;

use App\Models\AuditActivity;
use Spatie\Activitylog\Models\Concerns\LogsActivity;
use Spatie\Activitylog\Support\LogOptions;

/**
 * Records create, update, delete, and restore of the model in the audit log. A model lists the attributes whose
 * values must not be stored in `auditMaskedAttributes()`; those are only marked as changed. Only secrets belong there:
 * names and emails are stored as they are (D-0035).
 */
trait Audited
{
    use LogsActivity;

    private const AUDIT_MASK = '[disembunyikan]';

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logAll()
            ->logExcept(['remember_token', 'updated_at'])
            ->logOnlyDirty()
            ->dontLogEmptyChanges()
            ->useLogName($this->auditModule())
            ->setDescriptionForEvent(fn (string $event): string => $this->auditLabel().' '.match ($event) {
                'created' => 'dibuat',
                'updated' => 'diubah',
                'deleted' => 'dihapus',
                'restored' => 'dipulihkan',
                default => $event,
            });
    }

    /** Called by the activity log just before an entry is saved. */
    public function beforeActivityLogged(AuditActivity $activity, string $eventName): void
    {
        $changes = $activity->attribute_changes?->toArray() ?? [];

        foreach (['attributes', 'old'] as $side) {
            foreach ($this->auditMaskedAttributes() as $attribute) {
                if (array_key_exists($attribute, $changes[$side] ?? [])) {
                    $changes[$side][$attribute] = self::AUDIT_MASK;
                }
            }
        }

        $activity->attribute_changes = collect($changes);
    }

    /** @return list<string> */
    protected function auditMaskedAttributes(): array
    {
        return [];
    }

    /** The module column of the audit list; a model is its own module unless it says otherwise. */
    protected function auditModule(): string
    {
        return $this->auditLabel();
    }

    protected function auditLabel(): string
    {
        return class_basename($this);
    }
}
