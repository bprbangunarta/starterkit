<?php

namespace App\Http\Controllers\Settings;

use App\Actions\Settings\ListUsableStorages;
use App\Actions\Settings\UpdateProjectSettings;
use App\Enums\SettingKey;
use App\Enums\StorageType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\UpdateSettingsRequest;
use App\Models\StorageSetting;
use App\Services\ProjectSettings;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class SettingController extends Controller
{
    public function index(ProjectSettings $settings, ListUsableStorages $usable): Response
    {
        return Inertia::render('settings', [
            'settings' => [
                SettingKey::AppName->value => $settings->appName(),
                SettingKey::MetaDescription->value => (string) $settings->value(SettingKey::MetaDescription),
                SettingKey::ActiveStorage->value => $settings->activeStorage()->value,
            ],
            'images' => collect(SettingKey::imageValues())->mapWithKeys(fn (string $value): array => [$value => $this->imageOf(SettingKey::from($value), $settings)])->all(),
            'storages' => array_map(fn (StorageType $type): array => ['value' => $type->value, 'label' => StorageSetting::displayName($type)], $usable->handle()),
        ]);
    }

    /**
     * What the screen shows for an image: the address in force (uploaded, else bundled), whether it was uploaded, and the upload policy.
     *
     * @return array{url: string, custom: bool, mimes: list<string>, max_kb: int}
     */
    private function imageOf(SettingKey $kind, ProjectSettings $settings): array
    {
        $policy = $kind->imagePolicy();
        $uploaded = $settings->imageUrl($kind);

        return ['url' => $uploaded ?? $policy['bundled'], 'custom' => $uploaded !== null, 'mimes' => $policy['mimes'], 'max_kb' => $policy['max_kb']];
    }

    public function update(UpdateSettingsRequest $request, UpdateProjectSettings $update): RedirectResponse
    {
        $update->handle($request->validated());

        return back();
    }
}
