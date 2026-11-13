<?php

namespace App\Models;

use App\Models\Concerns\Audited;
use Database\Factories\SettingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * One project setting: the key is a value of SettingKey. Read through ProjectSettings, which caches and falls back to the default.
 *
 * @property int $id
 * @property string $key
 * @property string|null $value
 */
class Setting extends Model
{
    /** @use HasFactory<SettingFactory> */
    use Audited, HasFactory;

    /** @var list<string> */
    protected $fillable = ['key', 'value'];

    protected function auditLabel(): string
    {
        return 'Pengaturan';
    }
}
