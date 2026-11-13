<?php

namespace App\Models;

use App\Enums\StorageType;
use App\Models\Concerns\Audited;
use Database\Factories\StorageSettingFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $id
 * @property StorageType $type
 * @property string|null $name
 * @property array<string, mixed>|null $settings
 * @property Carbon|null $tested_at
 * @property bool|null $test_ok
 * @property string|null $test_message
 */
class StorageSetting extends Model
{
    /** @use HasFactory<StorageSettingFactory> */
    use Audited, HasFactory;

    /** @var list<string> */
    protected $fillable = ['type', 'name', 'settings'];

    /** The connection values are secrets: they never leave the server, and the audit trail only marks them as changed. */
    protected $hidden = ['settings'];

    /** @return array<string, mixed> */
    protected function casts(): array
    {
        return ['type' => StorageType::class, 'settings' => 'encrypted:array', 'tested_at' => 'datetime', 'test_ok' => 'boolean'];
    }

    /** The name a person gave the storage, or the name of its kind. */
    public static function displayName(StorageType $type): string
    {
        return static::query()->where('type', $type->value)->value('name') ?? $type->label();
    }

    /** @return list<string> */
    protected function auditMaskedAttributes(): array
    {
        return ['settings'];
    }

    protected function auditLabel(): string
    {
        return 'Penyimpanan';
    }
}
