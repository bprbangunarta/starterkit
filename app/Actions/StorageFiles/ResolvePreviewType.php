<?php

namespace App\Actions\StorageFiles;

use App\Enums\PreviewKind;

class ResolvePreviewType
{
    /**
     * The kind and content type a file is shown as, from its extension alone; null when the browser may not show it.
     *
     * @return array{kind: PreviewKind, mime: string}|null
     */
    public function handle(string $path): ?array
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));
        $type = config('storage_connections.preview_types')[$extension] ?? null;

        if ($type === null) {
            return null;
        }

        return ['kind' => PreviewKind::from($type[0]), 'mime' => $type[1]];
    }
}
