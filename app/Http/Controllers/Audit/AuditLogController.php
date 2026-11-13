<?php

namespace App\Http\Controllers\Audit;

use App\Enums\AuditResult;
use App\Http\Controllers\Controller;
use App\Http\Requests\Audit\AuditLogIndexRequest;
use App\Http\Resources\AuditEntryResource;
use App\Models\AuditActivity;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Inertia\Inertia;
use Inertia\Response;

class AuditLogController extends Controller
{
    public function index(AuditLogIndexRequest $request): Response
    {
        $filters = $request->filters();

        $entries = AuditActivity::query()
            ->with(['causer' => fn (MorphTo $causer) => $causer->withTrashed()])
            ->filtered($filters)
            ->sortedBy($filters['sort'], $filters['direction'])
            ->paginate($filters['per_page'])
            ->withQueryString();

        return Inertia::render('audit-logs', [
            'logs' => AuditEntryResource::collection($entries),
            'filters' => $filters,
            'modules' => AuditActivity::query()->whereNotNull('log_name')->distinct()->orderBy('log_name')->pluck('log_name')->all(),
            'results' => array_map(fn (AuditResult $result) => ['value' => $result->value, 'label' => $result->label()], AuditResult::cases()),
        ]);
    }
}
