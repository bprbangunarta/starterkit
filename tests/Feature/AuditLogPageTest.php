<?php

use App\Actions\Audit\RecordAudit;
use App\Enums\AuditEvent;
use App\Enums\AuditResult;
use App\Models\AuditActivity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Inertia\Testing\AssertableInertia;

uses(RefreshDatabase::class);

/** Signs in a user and clears the entries their own creation wrote, so each test counts only what it records. */
function signedInOnAuditPage(): User
{
    $user = User::factory()->superAdmin()->create();
    AuditActivity::query()->delete();

    test()->actingAs($user);

    return $user;
}

function recordAuditEntry(string $description, AuditEvent $event = AuditEvent::Login, AuditResult $result = AuditResult::Succeeded, ?User $causer = null, ?string $module = null): void
{
    app(RecordAudit::class)->handle($event, $description, causer: $causer, result: $result, module: $module);
}

function insertAuditRow(string $description, array $columns = []): void
{
    $now = now()->toDateTimeString();

    DB::table('activity_log')->insert([
        'log_name' => 'Autentikasi', 'description' => $description, 'result' => 'succeeded', 'created_at' => $now, 'updated_at' => $now, ...$columns,
    ]);
}

function auditDescriptionsSortedBy(string $sort, string $direction = 'asc'): array
{
    $page = test()->get(route('audit-logs', ['sort' => $sort, 'direction' => $direction]))->viewData('page');

    return array_column($page['props']['logs']['data'], 'description');
}

test('the audit page lists entries newest first with a fixed shape', function () {
    signedInOnAuditPage();
    $this->travelTo(Carbon::parse('2026-03-01 08:00'));
    recordAuditEntry('Pertama');
    $this->travelTo(Carbon::parse('2026-03-02 08:00'));
    recordAuditEntry('Kedua', AuditEvent::LoginFailed, AuditResult::Failed);

    $this->get(route('audit-logs'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->component('audit-logs', shouldExist: true)
            ->has('logs.data', 2)
            ->where('logs.data.0.description', 'Kedua')
            ->where('logs.data.0.module', 'Autentikasi')
            ->where('logs.data.0.event', ['value' => 'login_failed', 'label' => 'Gagal masuk'])
            ->where('logs.data.0.result', ['value' => 'failed', 'label' => 'Gagal'])
            ->where('logs.data.1.description', 'Pertama')
            ->has('logs.data.0', fn (AssertableInertia $entry) => $entry->hasAll(['id', 'created_at', 'module', 'event', 'result', 'description', 'causer', 'subject', 'ip_address', 'request_id', 'method', 'path', 'user_agent', 'reason', 'details', 'changes']))
            ->where('modules', ['Autentikasi'])
            ->has('results', count(AuditResult::cases())));
});

test('the list can be filtered by module and by result', function () {
    signedInOnAuditPage();
    recordAuditEntry('Masuk berhasil');
    recordAuditEntry('Data diubah', AuditEvent::Updated, module: 'Pengguna');
    recordAuditEntry('Terkunci', AuditEvent::Lockout, AuditResult::Refused);

    $this->get(route('audit-logs', ['module' => 'Pengguna']))
        ->assertInertia(fn (AssertableInertia $page) => $page->has('logs.data', 1)->where('logs.data.0.description', 'Data diubah'));

    $this->get(route('audit-logs', ['result' => 'refused']))
        ->assertInertia(fn (AssertableInertia $page) => $page->has('logs.data', 1)->where('logs.data.0.description', 'Terkunci'));
});

test('the list can be filtered by a date range that includes both end days', function () {
    signedInOnAuditPage();
    $this->travelTo(Carbon::parse('2026-03-01 00:00:30'));
    recordAuditEntry('Awal bulan');
    $this->travelTo(Carbon::parse('2026-03-31 23:59:30'));
    recordAuditEntry('Akhir bulan');
    $this->travelTo(Carbon::parse('2026-04-02 12:00'));
    recordAuditEntry('Bulan depan');

    $this->get(route('audit-logs', ['from' => '2026-03-01', 'to' => '2026-03-31']))
        ->assertInertia(fn (AssertableInertia $page) => $page->has('logs.data', 2)->where('logs.data.1.description', 'Awal bulan'));
});

test('the search matches part of a description and treats % and _ literally', function () {
    signedInOnAuditPage();
    recordAuditEntry('Diskon 50% disetujui', module: 'Kredit');
    recordAuditEntry('Kode a_b dipakai', module: 'Kredit');
    recordAuditEntry('Masuk biasa', module: 'Kredit');

    $this->get(route('audit-logs', ['search' => 'disetujui']))
        ->assertInertia(fn (AssertableInertia $page) => $page->has('logs.data', 1));
    $this->get(route('audit-logs', ['search' => '%']))
        ->assertInertia(fn (AssertableInertia $page) => $page->has('logs.data', 1)->where('logs.data.0.description', 'Diskon 50% disetujui'));
    $this->get(route('audit-logs', ['search' => '_']))
        ->assertInertia(fn (AssertableInertia $page) => $page->has('logs.data', 1)->where('logs.data.0.description', 'Kode a_b dipakai'));
});

test('the search also matches the actor, the module, and the IP address', function () {
    signedInOnAuditPage();
    $actor = User::factory()->create(['name' => 'Rina Wulandari', 'username' => 'rina.w']);
    recordAuditEntry('Oleh Rina', causer: $actor, module: 'Kredit');
    insertAuditRow('Dari luar', ['ip_address' => '203.0.113.9', 'log_name' => 'Jaringan']);
    recordAuditEntry('Lain', module: 'Gudang');

    foreach ([['wulandari', 'Oleh Rina'], ['rina.w', 'Oleh Rina'], ['jaringan', 'Dari luar'], ['203.0.113', 'Dari luar']] as [$term, $expected]) {
        $this->get(route('audit-logs', ['search' => $term]))
            ->assertInertia(fn (AssertableInertia $page) => $page->has('logs.data', 1)->where('logs.data.0.description', $expected));
    }
});

test('the list sorts by every column in either direction', function () {
    signedInOnAuditPage();
    $ani = User::factory()->create(['name' => 'Ani', 'username' => 'ani']);
    $budi = User::factory()->create(['name' => 'Budi', 'username' => 'budi']);
    AuditActivity::query()->delete();
    insertAuditRow('B', ['ip_address' => '10.0.0.2', 'log_name' => 'Zeta', 'event' => 'logout', 'result' => 'succeeded', 'causer_type' => User::class, 'causer_id' => $budi->id, 'subject_type' => User::class, 'subject_id' => $budi->id]);
    insertAuditRow('A', ['ip_address' => '10.0.0.1', 'log_name' => 'Alfa', 'event' => 'login', 'result' => 'failed', 'causer_type' => User::class, 'causer_id' => $ani->id, 'subject_type' => User::class, 'subject_id' => $ani->id]);

    foreach (['host', 'user', 'module', 'event', 'record', 'outcome'] as $sort) {
        expect(auditDescriptionsSortedBy($sort, 'asc'))->toBe(['A', 'B'], "{$sort} ascending")
            ->and(auditDescriptionsSortedBy($sort, 'desc'))->toBe(['B', 'A'], "{$sort} descending");
    }
});

test('an unknown sort key, direction, or page size falls back to the defaults', function () {
    signedInOnAuditPage();
    recordAuditEntry('Satu');

    $this->get(route('audit-logs', ['sort' => 'password', 'direction' => 'sideways', 'per_page' => 100000]))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('filters.sort', 'time')
            ->where('filters.direction', 'desc')
            ->where('filters.per_page', 25));
});

test('the page size is capped at 100 and pages carry the filters', function () {
    signedInOnAuditPage();
    $now = now()->toDateTimeString();
    DB::table('activity_log')->insert(array_map(fn (int $number) => [
        'log_name' => 'Autentikasi', 'description' => "Entri {$number}", 'result' => 'succeeded', 'created_at' => $now, 'updated_at' => $now,
    ], range(1, 30)));

    $this->get(route('audit-logs', ['per_page' => 10, 'page' => 2]))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->has('logs.data', 10)
            ->where('logs.meta.total', 30)
            ->where('logs.meta.current_page', 2)
            ->where('logs.meta.per_page', 10));
    $this->get(route('audit-logs', ['per_page' => 100]))
        ->assertInertia(fn (AssertableInertia $page) => $page->has('logs.data', 30)->where('filters.per_page', 100));
});

test('invalid filter values are refused', function (array $query, string $field) {
    $this->actingAs(User::factory()->superAdmin()->create())
        ->from(route('audit-logs'))
        ->get(route('audit-logs', $query))
        ->assertSessionHasErrors($field);
})->with([
    'module too long' => [['module' => str_repeat('a', 101)], 'module'],
    'unknown result' => [['result' => 'bogus'], 'result'],
    'bad date' => [['from' => '01-03-2026'], 'from'],
    'end before start' => [['from' => '2026-03-10', 'to' => '2026-03-01'], 'to'],
    'search too long' => [['search' => str_repeat('a', 101)], 'search'],
]);

test('the actor is shown even when their account was deleted', function () {
    signedInOnAuditPage();
    $actor = User::factory()->create(['name' => 'Rina Wulandari', 'username' => 'rina.w']);
    recordAuditEntry('Masuk', causer: $actor);
    $actor->delete();

    // Deleting the user wrote its own, newer entry, so the sign-in is the second one.
    $this->get(route('audit-logs'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('logs.data.1.description', 'Masuk')
            ->where('logs.data.1.causer.name', 'Rina Wulandari')
            ->where('logs.data.1.causer.username', 'rina.w'));
});

test('a model change shows its module and its old and new values, with the password masked', function () {
    signedInOnAuditPage();
    $user = User::factory()->create(['username' => 'lama']);
    AuditActivity::query()->delete();
    $user->update(['username' => 'baru', 'name' => 'Nama Baru']);

    $this->get(route('audit-logs'))
        ->assertInertia(fn (AssertableInertia $page) => $page
            ->where('logs.data.0.description', 'Pengguna diubah')
            ->where('logs.data.0.module', 'Pengguna')
            ->where('logs.data.0.subject', ['type' => 'Pengguna', 'id' => $user->id])
            ->where('logs.data.0.changes.old.username', 'lama')
            ->where('logs.data.0.changes.attributes.username', 'baru')
            ->where('logs.data.0.changes.attributes.name', 'Nama Baru'));
});

test('the number of queries does not grow with the number of entries', function () {
    signedInOnAuditPage();
    // The first request loads the roles of the signed-in user for the route check; only what follows is compared.
    test()->get(route('audit-logs'))->assertOk();
    $queriesFor = function (int $entries): int {
        AuditActivity::query()->delete();
        foreach (range(1, $entries) as $number) {
            recordAuditEntry("Entri {$number}", causer: User::factory()->create());
        }
        DB::flushQueryLog();
        DB::enableQueryLog();
        test()->get(route('audit-logs'))->assertOk();

        return count(DB::getQueryLog());
    };

    expect($queriesFor(3))->toBe($queriesFor(20));
});

test('an export request shows its row count and filters in the entry detail, and other entries show none', function () {
    signedInOnAuditPage();
    AuditActivity::query()->delete();
    activity('Audit')->event('exported')->withProperties(['rows' => 120, 'module' => 'Pengguna', 'result' => 'failed', 'from' => '2026-01-01', 'to' => null, 'search_used' => true])->log('Meminta ekspor catatan audit');
    activity('Autentikasi')->event('login')->log('Masuk');

    $details = fn (string $event) => collect(test()->get(route('audit-logs'))->viewData('page')['props']['logs']['data'])->firstWhere('event.value', $event)['details'];

    expect($details('exported'))->toBe([
        ['label' => 'Jumlah baris', 'value' => '120'],
        ['label' => 'Filter modul', 'value' => 'Pengguna'],
        ['label' => 'Filter hasil', 'value' => 'Gagal'],
        ['label' => 'Filter dari', 'value' => '2026-01-01'],
        ['label' => 'Filter sampai', 'value' => '–'],
        ['label' => 'Memakai pencarian', 'value' => 'Ya'],
    ])->and($details('login'))->toBe([]);
});
