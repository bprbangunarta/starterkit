<?php

use App\Models\User;
use Illuminate\Contracts\Http\Kernel as HttpKernel;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Middleware\TrustHosts;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Inertia\Testing\AssertableInertia;
use Symfony\Component\HttpFoundation\Exception\SuspiciousOperationException;

uses(RefreshDatabase::class);

/** The message the sign-in page gave under the login field; the session keeps the error bag as an array since Laravel 13. */
function loginError(): ?string
{
    return data_get(session('errors'), 'default.messages.login.0');
}

function signInAttempt(string $login, string $password = 'password'): array
{
    return ['login' => $login, 'password' => $password];
}

test('the login page renders for guests', function () {
    $this->get(route('login'))
        ->assertOk()
        ->assertInertia(fn (AssertableInertia $page) => $page->component('login', shouldExist: true));
});

test('a signed-in user is sent away from the login page', function () {
    $this->actingAs(User::factory()->create())
        ->get(route('login'))
        ->assertRedirect(route('dashboard'));
});

test('a user signs in with their username', function () {
    $user = User::factory()->create(['username' => 'rina.w']);

    $this->post(route('login.store'), signInAttempt('rina.w'))->assertRedirect(route('dashboard'));

    $this->assertAuthenticatedAs($user);
});

test('a user signs in with their email in any letter case', function () {
    $user = User::factory()->create(['email' => 'rina.w@example.test']);

    $this->post(route('login.store'), signInAttempt('Rina.W@Example.Test'))->assertRedirect(route('dashboard'));

    $this->assertAuthenticatedAs($user);
});

test('a user without a username can still sign in with their email', function () {
    $user = User::factory()->create(['username' => null, 'email' => 'tanpa.username@example.test']);

    $this->post(route('login.store'), signInAttempt('tanpa.username@example.test'))->assertRedirect(route('dashboard'));

    $this->assertAuthenticatedAs($user);
});

test('a wrong password is refused with the generic message and keeps the person signed out', function () {
    User::factory()->create(['username' => 'rina.w']);

    $this->post(route('login.store'), signInAttempt('rina.w', 'salah-banget'))
        ->assertSessionHasErrors(['login' => trans('auth.failed')]);

    $this->assertGuest();
});

test('an unknown account gets exactly the same message as a wrong password', function () {
    User::factory()->create(['username' => 'rina.w']);

    $this->post(route('login.store'), signInAttempt('rina.w', 'salah-banget'));
    $known = loginError();

    $this->post(route('login.store'), signInAttempt('hantu', 'salah-banget'));
    $unknown = loginError();

    expect($unknown)->toBe($known)->toBe(trans('auth.failed'));
});

test('empty input is refused with a message per field', function () {
    $this->post(route('login.store'), ['login' => '', 'password' => ''])
        ->assertSessionHasErrors(['login', 'password']);
});

test('oversized input is refused', function () {
    $this->post(route('login.store'), ['login' => str_repeat('a', 256), 'password' => str_repeat('a', 129)])
        ->assertSessionHasErrors(['login', 'password']);
});

test('signing in starts a new session id', function () {
    User::factory()->create(['username' => 'rina.w']);

    $this->get(route('login'));
    $before = session()->getId();

    $this->post(route('login.store'), signInAttempt('rina.w'));

    expect(session()->getId())->not->toBe($before);
});

test('after signing in the person lands on the page they asked for', function () {
    User::factory()->create(['username' => 'rina.w']);

    $this->get(route('audit-logs'))->assertRedirect(route('login'));

    $this->post(route('login.store'), signInAttempt('rina.w'))->assertRedirect(route('audit-logs'));
});

test('a deleted user cannot sign in', function () {
    User::factory()->create(['username' => 'rina.w'])->delete();

    $this->post(route('login.store'), signInAttempt('rina.w'))
        ->assertSessionHasErrors(['login' => trans('auth.failed')]);

    $this->assertGuest();
});

test('guests are sent to the login page from every protected page', function (string $route) {
    $this->get(route($route))->assertRedirect(route('login'));
})->with(['dashboard', 'ui-guideline', 'audit-logs']);

test('signing out ends the session and returns to the login page', function () {
    $this->actingAs(User::factory()->create())
        ->post(route('logout'))
        ->assertRedirect(route('login'));

    $this->assertGuest();
});

test('only signed-in users can sign out', function () {
    $this->post(route('logout'))->assertRedirect(route('login'));
});

test('five failed sign-ins lock the login, even for the right password', function () {
    User::factory()->create(['username' => 'rina.w']);

    foreach (range(1, 5) as $ignored) {
        $this->post(route('login.store'), signInAttempt('rina.w', 'salah-banget'));
    }

    $this->post(route('login.store'), signInAttempt('rina.w'));

    expect(loginError())->toContain('Terlalu banyak upaya masuk');
    $this->assertGuest();

    $this->travel(61)->seconds();

    $this->post(route('login.store'), signInAttempt('rina.w'))->assertRedirect(route('dashboard'));
});

test('a second lockout lasts longer than the first', function () {
    User::factory()->create(['username' => 'rina.w']);

    foreach (range(1, 5) as $ignored) {
        $this->post(route('login.store'), signInAttempt('rina.w', 'salah-banget'));
    }
    $this->travel(61)->seconds();

    foreach (range(1, 5) as $ignored) {
        $this->post(route('login.store'), signInAttempt('rina.w', 'salah-banget'));
    }
    $this->travel(61)->seconds();

    $this->post(route('login.store'), signInAttempt('rina.w'));
    expect(loginError())->toContain('Terlalu banyak upaya masuk');

    $this->travel(60)->seconds();

    $this->post(route('login.store'), signInAttempt('rina.w'))->assertRedirect(route('dashboard'));
});

test('failed sign-ins of one account do not lock another', function () {
    User::factory()->create(['username' => 'rina.w']);
    User::factory()->create(['username' => 'budi.s']);

    foreach (range(1, 5) as $ignored) {
        $this->post(route('login.store'), signInAttempt('rina.w', 'salah-banget'));
    }

    $this->post(route('login.store'), signInAttempt('budi.s'))->assertRedirect(route('dashboard'));
});

test('failures of an unknown account lock it like any other', function () {
    foreach (range(1, 5) as $ignored) {
        $this->post(route('login.store'), signInAttempt('hantu', 'salah-banget'));
    }

    $this->post(route('login.store'), signInAttempt('hantu'));

    expect(loginError())->toContain('Terlalu banyak upaya masuk');
});

test('a successful sign-in resets the failure count', function () {
    User::factory()->create(['username' => 'rina.w']);

    foreach (range(1, 4) as $ignored) {
        $this->post(route('login.store'), signInAttempt('rina.w', 'salah-banget'));
    }
    $this->post(route('login.store'), signInAttempt('rina.w'))->assertRedirect(route('dashboard'));
    $this->post(route('logout'));

    foreach (range(1, 4) as $ignored) {
        $this->post(route('login.store'), signInAttempt('rina.w', 'salah-banget'));
    }

    $this->post(route('login.store'), signInAttempt('rina.w'))->assertRedirect(route('dashboard'));
});

test('the login endpoint answers 429 after 20 requests a minute from one IP', function () {
    foreach (range(1, 20) as $number) {
        $this->post(route('login.store'), signInAttempt("hantu{$number}", 'salah-banget'));
    }

    $this->post(route('login.store'), signInAttempt('hantu21', 'salah-banget'))->assertStatus(429);
});

test('an expired page (419) goes to the login page with a message', function () {
    Route::middleware('web')->post('/halaman-kedaluwarsa', fn () => abort(419));

    $this->post('/halaman-kedaluwarsa')
        ->assertRedirect(route('login'))
        ->assertSessionHas('message', 'Halaman kedaluwarsa. Silakan coba lagi.');
});

test('the message of a redirect is shared with the page', function () {
    $this->withSession(['message' => 'Contoh pesan'])
        ->get(route('login'))
        ->assertInertia(fn (AssertableInertia $page) => $page->where('flash.message', 'Contoh pesan'));
});

test('the application answers only to its own address: the Host header cannot build the links of the app', function () {
    // The framework leaves trusted hosts off while tests run, so the middleware is asked directly, as it runs in production.
    $middleware = new class(app()) extends TrustHosts
    {
        protected function shouldSpecifyTrustedHosts(): bool
        {
            return true;
        }
    };

    try {
        expect(app(HttpKernel::class)->hasMiddleware(TrustHosts::class))->toBeTrue();
        expect(fn () => $middleware->handle(Request::create('http://penyerang.example/login'), fn (Request $request): string => $request->getHost()))
            ->toThrow(SuspiciousOperationException::class);
        expect($middleware->handle(Request::create(config('app.url').'/login'), fn (Request $request): string => $request->getHost()))->toBe(parse_url(config('app.url'), PHP_URL_HOST));
    } finally {
        Request::setTrustedHosts([]);
    }
});
