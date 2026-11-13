<?php

namespace App\Http\Middleware;

use App\Enums\RoleName;
use App\Enums\SettingKey;
use App\Models\User;
use App\Services\ProjectSettings;
use Illuminate\Http\Request;
use Illuminate\Notifications\DatabaseNotification;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    public function __construct(private readonly ProjectSettings $settings) {}

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'brand' => fn (): array => [
                'name' => $this->settings->appName(),
                'logo' => $this->settings->imageUrl(SettingKey::Logo),
                'favicon' => $this->settings->imageUrl(SettingKey::Favicon),
            ],
            'flash' => ['message' => fn (): ?string => $request->session()->get('message')],
            'auth' => fn (): array => [
                'user' => $request->user() === null ? null : [
                    ...$request->user()->only('id', 'name', 'username'),
                    'is_super_admin' => $request->user()->hasRole(RoleName::SuperAdmin->value),
                    'confirmed_until' => $this->confirmedUntil($request),
                ],
            ],
            'session' => fn (): array => ['idle_minutes' => $request->user() === null ? 0 : (int) config('session.lifetime')],
            'notifications' => fn (): array => $this->notificationsOf($request->user()),
        ];
    }

    /** The moment (unix seconds) until which a confirmed password still counts for sensitive actions; 0 when it was never confirmed. */
    private function confirmedUntil(Request $request): int
    {
        $confirmedAt = (int) $request->session()->get('auth.password_confirmed_at', 0);

        return $confirmedAt === 0 ? 0 : $confirmedAt + (int) config('auth.password_timeout');
    }

    /**
     * The unread count and the latest ten notices of the person, for the bell; resolved only when the page asks for it.
     *
     * @return array{unread: int, items: list<array<string, mixed>>}
     */
    private function notificationsOf(?User $user): array
    {
        if ($user === null) {
            return ['unread' => 0, 'items' => []];
        }

        return [
            'unread' => $user->unreadNotifications()->count(),
            'items' => $user->notifications()->latest()->limit(10)->get()->map(fn (DatabaseNotification $notification): array => [
                'id' => $notification->id,
                'title' => $notification->data['title'] ?? '',
                'body' => $notification->data['body'] ?? null,
                'level' => $notification->data['level'] ?? 'info',
                'url' => $notification->data['url'] ?? null,
                'read' => $notification->read_at !== null,
                'at' => $notification->created_at?->toIso8601String(),
            ])->all(),
        ];
    }
}
