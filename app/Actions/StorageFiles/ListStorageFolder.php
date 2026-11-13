<?php

namespace App\Actions\StorageFiles;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Pagination\LengthAwarePaginator;
use League\Flysystem\FileAttributes;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ListStorageFolder
{
    /**
     * One page of the folders and files of a place in a storage, folders first. The folder is read once, up to
     * `listing_max_entries` entries (a bigger folder is cut and says so); search, order, and page are applied to what was read.
     *
     * @param  array{search: ?string, sort: string, direction: string, per_page: int, page: int}  $filters
     * @return array{entries: LengthAwarePaginator<int, array<string, mixed>>, truncated: bool}
     */
    public function handle(FilesystemAdapter $disk, string $folder, array $filters): array
    {
        if ($folder !== '' && ! $disk->directoryExists($folder)) {
            throw new NotFoundHttpException('Folder tidak ditemukan.');
        }

        ['entries' => $entries, 'truncated' => $truncated] = $this->read($disk, $folder);
        $matching = $this->matching($entries, $filters['search']);
        usort($matching, $this->comparison($filters['sort'], $filters['direction']));

        $page = new LengthAwarePaginator(
            array_slice($matching, ($filters['page'] - 1) * $filters['per_page'], $filters['per_page']),
            count($matching),
            $filters['per_page'],
            $filters['page'],
        );

        return ['entries' => $page, 'truncated' => $truncated];
    }

    /** @return array{entries: list<array<string, mixed>>, truncated: bool} */
    private function read(FilesystemAdapter $disk, string $folder): array
    {
        $max = (int) config('storage_connections.listing_max_entries');
        $entries = [];

        foreach ($disk->getDriver()->listContents($folder, false) as $item) {
            if (count($entries) >= $max) {
                return ['entries' => $entries, 'truncated' => true];
            }

            $isFile = $item instanceof FileAttributes;

            $entries[] = [
                'name' => basename($item->path()),
                'path' => $item->path(),
                'type' => $isFile ? 'file' : 'folder',
                'size' => $isFile ? $item->fileSize() : null,
                'modified' => $item->lastModified() === null ? null : date(DATE_ATOM, $item->lastModified()),
            ];
        }

        return ['entries' => $entries, 'truncated' => false];
    }

    /**
     * @param  list<array<string, mixed>>  $entries
     * @return list<array<string, mixed>>
     */
    private function matching(array $entries, ?string $search): array
    {
        $term = mb_strtolower(trim((string) $search));

        return $term === '' ? $entries : array_values(array_filter($entries, fn (array $entry): bool => str_contains(mb_strtolower((string) $entry['name']), $term)));
    }

    /**
     * Folders before files, then the chosen key (unknown keys sort by name), then the name so that pages never repeat or skip an entry.
     *
     * @return callable(array<string, mixed>, array<string, mixed>): int
     */
    private function comparison(string $sort, string $direction): callable
    {
        $sign = $direction === 'desc' ? -1 : 1;
        $key = fn (array $entry): string|int => match ($sort) {
            'size' => (int) $entry['size'],
            'modified' => (string) $entry['modified'],
            'kind' => $entry['type'] === 'folder' ? '' : strtolower(pathinfo((string) $entry['name'], PATHINFO_EXTENSION)),
            default => mb_strtolower((string) $entry['name']),
        };

        return fn (array $a, array $b): int => $a['type'] === $b['type']
            ? ($sign * ($key($a) <=> $key($b))) ?: strcasecmp((string) $a['name'], (string) $b['name'])
            : ($a['type'] === 'folder' ? -1 : 1);
    }
}
