<?php

use App\Http\Middleware\SetSecurityHeaders;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;

uses(RefreshDatabase::class);

test('every answer of the app, the sign-in page and the error pages included, carries the protective headers', function () {
    foreach ([$this->get(route('login')), $this->get('/tidak-ada-halaman')] as $response) {
        expect($response->headers->get('X-Content-Type-Options'))->toBe('nosniff')
            ->and($response->headers->get('Referrer-Policy'))->toBe('strict-origin-when-cross-origin')
            ->and($response->headers->get('Content-Security-Policy'))->toContain("frame-ancestors 'self'")->toContain("object-src 'none'");
    }
});

test('what a signed-in person is shown is not kept by any cache, and what a guest is shown does not need to be', function () {
    expect($this->get(route('login'))->headers->get('Cache-Control'))->not->toContain('no-store');

    $this->actingAs(User::factory()->create());

    expect($this->get(route('profile'))->assertOk()->headers->get('Cache-Control'))->toContain('no-store')
        ->and($this->get('/tidak-ada-halaman')->headers->get('Cache-Control'))->toContain('no-store');
});

test('an answer that chose to be public is left that way, and a policy that was set for a reason is kept', function () {
    // Signed in, so that the public choice is what keeps the answer from `no-store`.
    $request = Request::create('/');
    $request->setUserResolver(fn () => User::factory()->make());

    $response = (new SetSecurityHeaders)->handle(
        $request,
        fn () => response('x', 200, ['Cache-Control' => 'public, max-age=31536000, immutable', 'Content-Security-Policy' => "default-src 'none'; sandbox"]),
    );

    expect($response->headers->get('Cache-Control'))->toContain('public')->not->toContain('no-store')
        ->and($response->headers->get('Content-Security-Policy'))->toBe("default-src 'none'; sandbox")
        ->and($response->headers->get('X-Content-Type-Options'))->toBe('nosniff');
});

test('an error raised by another global middleware carries the headers too, since this middleware is the outermost', function () {
    // A body bigger than post_max_size is refused by a middleware that runs before the router.
    $response = $this->call('POST', route('login.store'), [], [], [], ['CONTENT_LENGTH' => 9_999_999_999]);

    expect($response->getStatusCode())->toBe(413)
        ->and($response->headers->get('X-Content-Type-Options'))->toBe('nosniff')
        ->and($response->headers->get('Content-Security-Policy'))->toContain("frame-ancestors 'self'");
});
