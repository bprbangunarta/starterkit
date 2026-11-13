<?php

namespace App\Http\Resources;

use App\Enums\AuditEvent;
use App\Enums\AuditResult;
use App\Models\AuditActivity;
use App\Models\Office;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Spatie\Permission\Models\Role;

/** @mixin AuditActivity */
class AuditEntryResource extends JsonResource
{
    /** @return array<string, mixed> */
    public function toArray(Request $request): array
    {
        $changes = $this->attribute_changes?->toArray() ?? [];

        return [
            'id' => $this->id,
            'created_at' => $this->created_at->toIso8601String(),
            'event' => ['value' => $this->event, 'label' => AuditEvent::tryFrom((string) $this->event)?->label() ?? $this->event],
            'result' => ['value' => $this->result->value, 'label' => $this->result->label()],
            'description' => $this->description,
            'module' => $this->log_name,
            'causer' => $this->causer instanceof User ? ['id' => $this->causer->id, 'name' => $this->causer->name, 'username' => $this->causer->username] : null,
            'subject' => $this->subject_type === null ? null : ['type' => $this->subjectLabel($this->subject_type), 'id' => $this->subject_id],
            'ip_address' => $this->ip_address,
            'request_id' => $this->request_id,
            'method' => $this->method,
            'path' => $this->path,
            'user_agent' => $this->user_agent,
            'details' => $this->details(),
            'reason' => $this->properties['reason'] ?? null,
            'changes' => ['old' => $changes['old'] ?? [], 'attributes' => $changes['attributes'] ?? []],
        ];
    }

    /**
     * Facts of an entry that are not a before and after, as label and value rows; only an export request carries them.
     *
     * @return list<array{label: string, value: string}>
     */
    private function details(): array
    {
        if ($this->event !== AuditEvent::Exported->value) {
            return [];
        }

        $properties = $this->properties;
        $result = AuditResult::tryFrom((string) ($properties['result'] ?? ''));

        return [
            ['label' => 'Jumlah baris', 'value' => (string) ($properties['rows'] ?? '–')],
            ['label' => 'Filter modul', 'value' => $properties['module'] ?? '–'],
            ['label' => 'Filter hasil', 'value' => $result?->label() ?? '–'],
            ['label' => 'Filter dari', 'value' => $properties['from'] ?? '–'],
            ['label' => 'Filter sampai', 'value' => $properties['to'] ?? '–'],
            ['label' => 'Memakai pencarian', 'value' => ($properties['search_used'] ?? false) ? 'Ya' : 'Tidak'],
        ];
    }

    private function subjectLabel(string $type): string
    {
        return match ($type) {
            User::class => 'Pengguna',
            Role::class => 'Jabatan',
            Office::class => 'Kantor',
            default => class_basename($type),
        };
    }
}
