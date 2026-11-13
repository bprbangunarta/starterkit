<?php

use App\Actions\Audit\RecordAudit;
use App\Actions\Audit\WriteAuditExport;
use App\Enums\AuditEvent;
use App\Enums\AuditExportStatus;
use App\Enums\AuditResult;
use App\Jobs\GenerateAuditExport;
use App\Models\AuditActivity;
use App\Models\AuditExport;
use App\Models\User;
use App\Notifications\AuditExportFailed;
use App\Notifications\AuditExportReady;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;

uses(RefreshDatabase::class);

/** The account's own creation is audited too, so the log is emptied before the entries of the test are made. */
function signedInUser(): User
{
    $user = User::factory()->superAdmin()->create();
    AuditActivity::query()->delete();

    return $user;
}

function recordedEntry(string $description, ?string $module = null): void
{
    app(RecordAudit::class)->handle(AuditEvent::Logout, $description, module: $module);
}

/** @return list<list<string|null>> */
function rowsOf(string $content): array
{
    $lines = str_getcsv(str_replace("\xEF\xBB\xBF", '', $content), "\n", escape: '');

    return array_map(fn (string $line): array => str_getcsv($line, escape: ''), array_filter($lines));
}

/** Requests an export as the person would, then lets the queue job write the file. */
function exportedFile(User $user, array $query = []): string
{
    test()->actingAs($user)->post(route('audit-logs.exports.store'), $query);

    $export = AuditExport::query()->latest('id')->firstOrFail();
    (new GenerateAuditExport($export))->handle(app(WriteAuditExport::class));

    return Storage::get($export->fresh()->path);
}

beforeEach(fn () => Storage::fake());

test('guests can neither request nor download an export', function () {
    $this->post(route('audit-logs.exports.store'))->assertRedirect(route('login'));
    $this->get(route('audit-logs.exports.show', 'abc'))->assertRedirect(route('login'));
});

test('asking for an export returns at once and leaves the work to the queue', function () {
    Queue::fake();
    $user = signedInUser();
    recordedEntry('Satu');

    $this->actingAs($user)->post(route('audit-logs.exports.store'), ['module' => 'Autentikasi'])->assertRedirect();

    $export = AuditExport::query()->sole();
    expect($export->user_id)->toBe($user->id)
        ->and($export->status)->toBe(AuditExportStatus::Pending)
        ->and($export->rows)->toBe(1)
        ->and($export->filters['module'])->toBe('Autentikasi');
    Queue::assertPushed(GenerateAuditExport::class, fn (GenerateAuditExport $job): bool => $job->export->is($export));
});

test('a request is recorded with its filters but without the typed search text', function () {
    Queue::fake();
    $user = signedInUser();
    recordedEntry('Rina Wulandari keluar');

    $this->actingAs($user)->post(route('audit-logs.exports.store'), ['search' => 'Rina Wulandari', 'result' => 'succeeded']);

    $entry = AuditActivity::query()->where('event', 'exported')->sole();
    expect($entry->causer_id)->toBe($user->id)
        ->and($entry->subject_id)->toBeNull()
        ->and($entry->properties['export_id'])->toBe(AuditExport::query()->sole()->id)
        ->and($entry->result)->toBe(AuditResult::Succeeded)
        ->and($entry->properties['rows'])->toBe(1)
        ->and($entry->properties['result'])->toBe('succeeded')
        ->and($entry->properties['search_used'])->toBeTrue()
        ->and(json_encode($entry->properties))->not->toContain('Rina Wulandari');
});

test('the export record itself adds no audit entry of its own', function () {
    $export = AuditExport::factory()->create();
    $export->update(['status' => AuditExportStatus::Failed]);

    expect(AuditActivity::query()->where('subject_type', AuditExport::class)->count())->toBe(0);
});

test('a filter that matches more than the limit is refused and nothing is queued', function () {
    Queue::fake();
    config(['activitylog.export_max_rows' => 2]);
    $user = signedInUser();
    foreach (range(1, 3) as $number) {
        recordedEntry("Entri {$number}");
    }

    $this->actingAs($user)->from(route('audit-logs'))->post(route('audit-logs.exports.store'))->assertSessionHasErrors('export');

    expect(AuditExport::query()->count())->toBe(0)
        ->and(AuditActivity::query()->where('event', 'exported')->count())->toBe(0);
    Queue::assertNothingPushed();
});

test('a person who already holds the most exports is refused a new one, and others are not', function () {
    Queue::fake();
    config(['activitylog.export_max_active_per_user' => 2]);
    $user = signedInUser();
    AuditExport::factory()->count(2)->for($user)->create();
    AuditExport::factory()->for($user)->expired()->create();
    AuditExport::factory()->for($user)->failed()->create();

    $this->actingAs($user)->from(route('audit-logs'))->post(route('audit-logs.exports.store'))->assertSessionHasErrors('export');
    expect(AuditExport::query()->count())->toBe(4);

    $this->actingAs(User::factory()->superAdmin()->create())->post(route('audit-logs.exports.store'))->assertSessionHasNoErrors();
    expect(AuditExport::query()->count())->toBe(5);
});

test('nobody can ask for a new export while the whole system holds the most', function () {
    Queue::fake();
    config(['activitylog.export_max_active' => 2]);
    AuditExport::factory()->count(2)->create();

    $this->actingAs(User::factory()->superAdmin()->create())->post(route('audit-logs.exports.store'))->assertSessionHasErrors('export');

    expect(AuditExport::query()->count())->toBe(2);
});

test('requests for an export are rate limited', function () {
    Queue::fake();
    config(['activitylog.export_max_active_per_user' => 100, 'activitylog.export_max_active' => 100]);
    $user = signedInUser();

    foreach (range(1, 6) as $attempt) {
        $this->actingAs($user)->post(route('audit-logs.exports.store'));
    }

    $this->actingAs($user)->post(route('audit-logs.exports.store'))->assertTooManyRequests();
});

test('an invalid date range is refused', function () {
    $this->actingAs(signedInUser())
        ->post(route('audit-logs.exports.store'), ['from' => '2026-11-10', 'to' => '2026-11-01'])
        ->assertSessionHasErrors('to');
});

test('the file is a CSV with a header row, the newest entry first, and not the export request itself', function () {
    $user = signedInUser();
    recordedEntry('Pertama');
    recordedEntry('Kedua');

    $content = exportedFile($user);

    expect($content)->toStartWith("\xEF\xBB\xBF");
    $rows = rowsOf($content);
    expect($rows)->toHaveCount(3)
        ->and($rows[0][0])->toBe('ID')
        ->and($rows[1][9])->toBe('Kedua')
        ->and($rows[2][9])->toBe('Pertama');
});

test('the file follows the filters of the request', function () {
    $user = signedInUser();
    recordedEntry('Di modul A', 'Modul A');
    recordedEntry('Di modul B', 'Modul B');

    $rows = rowsOf(exportedFile($user, ['module' => 'Modul A']));

    expect($rows)->toHaveCount(2)->and($rows[1][9])->toBe('Di modul A');
});

test('a cell that would run as a spreadsheet formula is neutralized', function (string $description) {
    $user = signedInUser();
    recordedEntry($description);

    expect(rowsOf(exportedFile($user, ['search' => 'cmd']))[1][9])->toBe("'".$description);
})->with(['=cmd|calc', '+cmd', '-cmd', '@cmd']);

test('the person is told when the file is ready, and running the job twice changes nothing', function () {
    Notification::fake();
    $user = signedInUser();
    recordedEntry('Satu');
    $this->actingAs($user)->post(route('audit-logs.exports.store'));
    $export = AuditExport::query()->sole();
    $job = new GenerateAuditExport($export);

    $job->handle(app(WriteAuditExport::class));
    $job->handle(app(WriteAuditExport::class));

    expect($export->fresh()->status)->toBe(AuditExportStatus::Ready);
    Notification::assertSentToTimes($user, AuditExportReady::class, 1);
});

test('after the last retry the export is marked failed and the person is told', function () {
    Notification::fake();
    $export = AuditExport::factory()->create();

    (new GenerateAuditExport($export))->failed();

    expect($export->fresh()->status)->toBe(AuditExportStatus::Failed);
    Notification::assertSentTo($export->user, AuditExportFailed::class);
});

test('the person downloads their own ready file', function () {
    $export = AuditExport::factory()->ready()->create();
    Storage::put($export->path, "isi,csv\n");

    $this->actingAs($export->user)->get(route('audit-logs.exports.show', $export))->assertOk()->assertDownload();
});

test('nobody else can download it, and a pending, failed, expired, or missing file is not found', function (string $case) {
    $export = match ($case) {
        'someone else' => AuditExport::factory()->ready()->create(),
        'pending' => AuditExport::factory()->create(),
        'failed' => AuditExport::factory()->failed()->create(),
        'expired' => AuditExport::factory()->ready()->expired()->create(),
        'file gone' => AuditExport::factory()->ready()->create(),
    };
    $viewer = $case === 'someone else' ? User::factory()->superAdmin()->create() : $export->user;
    if ($case !== 'file gone') {
        Storage::put($export->path ?? 'audit-exports/x.csv', 'x');
    }

    $this->actingAs($viewer)->get(route('audit-logs.exports.show', $export))->assertNotFound();
})->with(['someone else', 'pending', 'failed', 'expired', 'file gone']);

test('pruning deletes expired exports with their files and keeps the others', function () {
    $old = AuditExport::factory()->ready()->expired()->create();
    $current = AuditExport::factory()->ready()->create();
    Storage::put($old->path, 'x');
    Storage::put($current->path, 'x');

    $this->artisan('audit:prune-exports', ['--force' => true])->assertSuccessful();

    expect(AuditExport::query()->find($old->id))->toBeNull()
        ->and(AuditExport::query()->find($current->id))->not->toBeNull();
    Storage::assertMissing($old->path);
    Storage::assertExists($current->path);
});
