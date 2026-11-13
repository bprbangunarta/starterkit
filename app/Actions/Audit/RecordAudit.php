<?php

namespace App\Actions\Audit;

use App\Enums\AuditEvent;
use App\Enums\AuditResult;
use App\Models\AuditActivity;
use Illuminate\Database\Eloquent\Model;

class RecordAudit
{
    /**
     * Writes one audit entry for something that is not a model change. Without a causer the actor is anonymous, and the module defaults to the event's own.
     *
     * @param  array<string, mixed>  $properties
     * @param  array{old?: array<string, mixed>, attributes?: array<string, mixed>}  $changes  Before and after values, shown in the entry's detail.
     */
    public function handle(
        AuditEvent $event,
        string $description,
        ?Model $causer = null,
        ?Model $subject = null,
        array $properties = [],
        AuditResult $result = AuditResult::Succeeded,
        ?string $module = null,
        array $changes = [],
    ): void {
        $logger = activity($module ?? $event->defaultModule())->event($event->value)->withProperties($properties);

        $causer === null ? $logger->causedByAnonymous() : $logger->causedBy($causer);

        if ($changes !== []) {
            $logger->withChanges($changes);
        }

        if ($subject !== null) {
            $logger->performedOn($subject);
        }

        $logger
            ->tap(function (AuditActivity $activity) use ($result): void {
                $activity->result = $result;
            })
            ->log($description);
    }
}
