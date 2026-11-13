<?php

namespace App\Actions\Settings;

use App\Enums\SettingKey;
use App\Models\Setting;
use App\Services\ProjectSettings;
use Illuminate\Support\Facades\DB;

class UpdateProjectSettings
{
    public function __construct(private readonly ProjectSettings $settings) {}

    /**
     * Saves the given settings, one row per key, and forgets the cached map. An empty value takes the key back to its default.
     *
     * @param  array<string, string|null>  $values  keyed by SettingKey value
     */
    public function handle(array $values): void
    {
        DB::transaction(function () use ($values): void {
            foreach (SettingKey::cases() as $key) {
                if (! array_key_exists($key->value, $values)) {
                    continue;
                }

                $row = Setting::query()->where('key', $key->value)->lockForUpdate()->first() ?? new Setting(['key' => $key->value]);
                $row->value = filled($values[$key->value]) ? $values[$key->value] : null;
                $row->save();
            }
        });

        $this->settings->forget();
    }
}
