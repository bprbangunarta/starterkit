<?php

namespace App\Http\Requests\Storage;

use App\Actions\Storage\AssertHostAllowed;
use App\Enums\StorageType;
use App\Models\StorageSetting;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Validator;

class SaveStorageRequest extends FormRequest
{
    // The route is limited to the Super Admin role (D-0029).
    public function authorize(): bool
    {
        return true;
    }

    /**
     * The secrets are optional here because an empty one keeps what was saved; `after()` requires them when nothing was saved yet.
     *
     * @return array<string, list<mixed>>
     */
    public function rules(): array
    {
        return match ($this->storageType()) {
            // Never reached: the route only takes the kinds that can be configured.
            StorageType::Local => [],
            StorageType::S3 => [
                'key' => ['nullable', 'string', 'max:128'],
                'secret' => ['nullable', 'string', 'max:255'],
                'region' => ['required', 'string', 'max:50', 'regex:/^[A-Za-z0-9-]+$/'],
                'bucket' => ['required', 'string', 'min:3', 'max:63', 'regex:/^[a-z0-9][a-z0-9.-]*$/'],
                'endpoint' => ['nullable', 'string', 'url:http,https', 'max:255'],
                'use_path_style' => ['boolean'],
                'root' => ['nullable', 'string', 'max:200', 'not_regex:/(^|\/)\.\.(\/|$)/'],
            ],
            StorageType::Sftp => [
                'host' => ['required', 'string', 'max:255', 'regex:/^[A-Za-z0-9.-]+$|^[0-9A-Fa-f:]+$/'],
                'port' => ['required', 'integer', 'between:1,65535'],
                'username' => ['required', 'string', 'max:100'],
                'password' => ['nullable', 'string', 'max:255'],
                'private_key' => ['nullable', 'string', 'max:8192', 'regex:/^\s*-----BEGIN [A-Z ]*PRIVATE KEY-----/'],
                'root' => ['nullable', 'string', 'max:200', 'not_regex:/(^|\/)\.\.(\/|$)/'],
            ],
        };
    }

    /** @return array<string, string> */
    public function attributes(): array
    {
        return [
            'key' => 'access key',
            'secret' => 'secret key',
            'region' => 'region',
            'bucket' => 'bucket',
            'endpoint' => 'endpoint',
            'root' => 'folder dasar',
            'host' => 'host',
            'port' => 'port',
            'username' => 'username',
            'password' => 'kata sandi',
            'private_key' => 'kunci privat',
        ];
    }

    /** @return list<callable(Validator): void> */
    public function after(): array
    {
        return [
            function (Validator $validator): void {
                if ($validator->errors()->isNotEmpty()) {
                    return;
                }

                $this->requireSecrets($validator);
                $this->refuseInternalHost($validator);
            },
        ];
    }

    private function storageType(): StorageType
    {
        $type = $this->route('type');

        return $type instanceof StorageType ? $type : StorageType::from((string) $type);
    }

    /** Nothing was saved yet, or the saved values lack it: the first save must bring the secrets. */
    private function requireSecrets(Validator $validator): void
    {
        $saved = (array) StorageSetting::query()->where('type', $this->storageType()->value)->first()?->settings;
        $has = fn (string $field): bool => filled($this->input($field)) || filled($saved[$field] ?? null);

        if ($this->storageType() === StorageType::S3) {
            foreach (['key' => 'Access key', 'secret' => 'Secret key'] as $field => $label) {
                if (! $has($field)) {
                    $validator->errors()->add($field, "{$label} wajib diisi.");
                }
            }

            return;
        }

        if (! $has('password') && ! $has('private_key')) {
            $validator->errors()->add('password', 'Isi kata sandi atau kunci privat.');
        }
    }

    /** The host typed here is an address the server will connect to, so it must not point inside the network unless it was listed. */
    private function refuseInternalHost(Validator $validator): void
    {
        $isSftp = $this->storageType() === StorageType::Sftp;
        $host = $isSftp ? (string) $this->input('host') : (string) parse_url((string) $this->input('endpoint'), PHP_URL_HOST);

        if ($host === '') {
            return;
        }

        $refusal = app(AssertHostAllowed::class)->refusal($host);

        if ($refusal !== null) {
            $validator->errors()->add($isSftp ? 'host' : 'endpoint', $refusal);
        }
    }
}
