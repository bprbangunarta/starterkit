<?php

use App\Actions\Settings\UpdateProjectSettings;
use App\Actions\Storage\BuildStorageDisk;
use App\Enums\SettingKey;
use App\Models\Setting;
use App\Models\User;
use App\Services\ProjectSettings;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia;

uses(RefreshDatabase::class);

/** A Super Admin who confirmed the password, and a throwaway disk in place of the active storage: the tests never touch a real storage. */
function brandDisk(): FilesystemAdapter
{
    $admin = User::factory()->superAdmin()->create();
    test()->actingAs($admin)->withSession(['auth.password_confirmed_at' => now()->timestamp]);
    $disk = Storage::fake('uji-merek');
    test()->mock(BuildStorageDisk::class)->shouldReceive('forFiles')->andReturn($disk);

    return $disk;
}

/** @return list<string> */
function brandFiles(FilesystemAdapter $disk): array
{
    return $disk->files('branding');
}

test('an uploaded logo is kept in the active storage and shown on every page, signed in or not', function () {
    $disk = brandDisk();

    $this->post(route('settings.images.store', 'logo'), ['file' => UploadedFile::fake()->image('logo.png', 64, 64)])->assertRedirect();

    $files = brandFiles($disk);
    expect($files)->toHaveCount(1)->and($files[0])->toStartWith('branding/logo-')->toEndWith('.png');

    $url = app(ProjectSettings::class)->imageUrl(SettingKey::Logo);
    expect($url)->toStartWith(route('brand.image', 'logo').'?v=');

    $this->get(route('dashboard'))->assertInertia(fn (AssertableInertia $page) => $page->where('brand.logo', $url));
    auth()->logout();
    $this->get(route('login'))->assertInertia(fn (AssertableInertia $page) => $page->where('brand.logo', $url));
});

test('the image is served to anyone with its type taken from the extension and safe headers', function () {
    $disk = brandDisk();
    $this->post(route('settings.images.store', 'logo'), ['file' => UploadedFile::fake()->image('logo.png', 64, 64)]);
    auth()->logout();

    $response = $this->get(route('brand.image', 'logo'))->assertOk();

    expect($response->streamedContent())->toBe($disk->get(brandFiles($disk)[0]))
        ->and($response->headers->get('Content-Type'))->toBe('image/png')
        ->and($response->headers->get('X-Content-Type-Options'))->toBe('nosniff')
        ->and($response->headers->get('Content-Security-Policy'))->toContain('sandbox')
        ->and($response->headers->get('Cache-Control'))->toContain('public')->toContain('immutable');
});

test('a logo that was never uploaded, or whose file is gone, is not found', function () {
    $disk = brandDisk();

    $this->get(route('brand.image', 'logo'))->assertNotFound();

    $this->post(route('settings.images.store', 'logo'), ['file' => UploadedFile::fake()->image('logo.png', 64, 64)]);
    $disk->delete(brandFiles($disk)[0]);

    $this->get(route('brand.image', 'logo'))->assertNotFound();
});

test('only the logo and the favicon can be served', function () {
    brandDisk();

    $this->get('/brand/app_name')->assertNotFound();
});

test('a new image replaces the old one, and the old file is removed', function () {
    $disk = brandDisk();
    $this->post(route('settings.images.store', 'favicon'), ['file' => UploadedFile::fake()->image('a.png', 32, 32)]);
    $first = brandFiles($disk)[0];
    $firstUrl = app(ProjectSettings::class)->imageUrl(SettingKey::Favicon);

    $this->post(route('settings.images.store', 'favicon'), ['file' => UploadedFile::fake()->image('b.png', 32, 32)]);

    expect(brandFiles($disk))->toHaveCount(1)->not->toContain($first)
        ->and(app(ProjectSettings::class)->imageUrl(SettingKey::Favicon))->not->toBe($firstUrl);
});

test('removing an image takes the page back to the bundled one and deletes the file', function () {
    $disk = brandDisk();
    $this->post(route('settings.images.store', 'logo'), ['file' => UploadedFile::fake()->image('logo.png', 64, 64)]);

    $this->delete(route('settings.images.destroy', 'logo'))->assertRedirect();

    expect(brandFiles($disk))->toBe([])
        ->and(app(ProjectSettings::class)->imageUrl(SettingKey::Logo))->toBeNull()
        ->and(Setting::query()->where('key', 'logo')->value('value'))->toBeNull();
    $this->get(route('settings'))->assertInertia(fn (AssertableInertia $page) => $page->where('images.logo.custom', false)->where('images.logo.url', '/logo.png'));
});

test('the upload is checked: it must be a small image of an allowed type', function (string $kind, UploadedFile $file) {
    $disk = brandDisk();

    $this->post(route('settings.images.store', $kind), ['file' => $file])->assertSessionHasErrors('file');

    expect(brandFiles($disk))->toBe([]);
})->with([
    'a text file as the logo' => ['logo', fn () => UploadedFile::fake()->create('x.txt', 1, 'text/plain')],
    'an SVG logo, which can run script' => ['logo', fn () => UploadedFile::fake()->createWithContent('logo.svg', '<svg xmlns="http://www.w3.org/2000/svg"/>')],
    'a logo over 512 KB' => ['logo', fn () => UploadedFile::fake()->create('logo.png', 513, 'image/png')],
    'a favicon over 128 KB' => ['favicon', fn () => UploadedFile::fake()->create('favicon.png', 129, 'image/png')],
    'a JPEG favicon' => ['favicon', fn () => UploadedFile::fake()->image('favicon.jpg', 32, 32)],
]);

test('an upload of a kind that is not an image setting is not found', function () {
    brandDisk();

    $this->post('/settings/images/app_name', ['file' => UploadedFile::fake()->image('x.png')])->assertNotFound();
});

test('only a Super Admin who just confirmed the password can change the images', function () {
    $this->post('/settings/images/logo')->assertRedirect(route('login'));

    $this->actingAs(User::factory()->create());
    $this->post(route('settings.images.store', 'logo'), ['file' => UploadedFile::fake()->image('l.png')])->assertForbidden();
    $this->delete(route('settings.images.destroy', 'logo'))->assertForbidden();

    $this->actingAs(User::factory()->superAdmin()->create());
    $this->postJson(route('settings.images.store', 'logo'), ['file' => UploadedFile::fake()->image('l.png')])->assertStatus(423);
    $this->deleteJson(route('settings.images.destroy', 'logo'))->assertStatus(423);
});

test('the settings page tells the policy of each image', function () {
    brandDisk();

    $this->get(route('settings'))->assertInertia(fn (AssertableInertia $page) => $page
        ->where('images.logo', ['url' => '/logo.png', 'custom' => false, 'mimes' => ['png', 'jpg', 'jpeg', 'webp'], 'max_kb' => 512])
        ->where('images.favicon', ['url' => '/favicon.ico', 'custom' => false, 'mimes' => ['png', 'ico'], 'max_kb' => 128]));
});

test('an image that could not be recorded is not left behind in the storage', function () {
    $disk = brandDisk();
    $this->mock(UpdateProjectSettings::class)->shouldReceive('handle')->andThrow(new RuntimeException('basis data mati'));

    $this->post(route('settings.images.store', 'logo'), ['file' => UploadedFile::fake()->image('logo.png', 64, 64)])->assertStatus(500);

    expect(brandFiles($disk))->toBe([]);
});
