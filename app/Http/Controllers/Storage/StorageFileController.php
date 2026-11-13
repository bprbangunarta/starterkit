<?php

namespace App\Http\Controllers\Storage;

use App\Actions\Storage\RunOnStorage;
use App\Actions\StorageFiles\DeleteStorageItem;
use App\Actions\StorageFiles\ListStorageFolder;
use App\Actions\StorageFiles\RenameStorageFile;
use App\Actions\StorageFiles\UploadStorageFile;
use App\Enums\StorageType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Storage\RenameStorageFileRequest;
use App\Http\Requests\Storage\StorageFileIndexRequest;
use App\Http\Requests\Storage\StorageItemRequest;
use App\Http\Requests\Storage\UploadStorageFileRequest;
use App\Http\Resources\StorageItemResource;
use App\Models\StorageSetting;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class StorageFileController extends Controller
{
    /** The folders and files of a place in a storage. A storage that cannot be reached shows its reason in the page instead of failing. */
    public function index(StorageFileIndexRequest $request, StorageType $type, RunOnStorage $run, ListStorageFolder $list): Response
    {
        $filters = $request->filters();
        $listing = null;
        $error = null;

        try {
            $listing = $run->handle($type, fn (FilesystemAdapter $disk): array => $list->handle($disk, $request->folder(), $filters));
        } catch (ValidationException $exception) {
            $error = $exception->errors()['storage'][0] ?? 'Penyimpanan tidak dapat dipakai.';
        }

        return Inertia::render('storage-files', [
            'storage' => ['type' => $type->value, 'label' => StorageSetting::displayName($type)],
            'path' => $request->folder(),
            'files' => $listing === null ? null : StorageItemResource::collection($listing['entries']->withQueryString()),
            'filters' => $filters,
            'truncated' => $listing['truncated'] ?? false,
            'error' => $error,
            'limits' => [
                'max_mb' => (int) config('storage_connections.upload_max_kb') / 1024,
                'extensions' => config('storage_connections.upload_extensions'),
                'preview' => collect(config('storage_connections.preview_types'))->map(fn (array $type): string => $type[0])->all(),
                'preview_max_mb' => (int) config('storage_connections.preview_max_kb') / 1024,
            ],
        ]);
    }

    public function store(UploadStorageFileRequest $request, StorageType $type, RunOnStorage $run, UploadStorageFile $upload): RedirectResponse
    {
        $run->handle($type, fn (FilesystemAdapter $disk): string => $upload->handle($type, $disk, (string) $request->validated('path'), $request->file('file'), $request->user()));

        return back();
    }

    public function update(RenameStorageFileRequest $request, StorageType $type, RunOnStorage $run, RenameStorageFile $rename): RedirectResponse
    {
        $run->handle($type, fn (FilesystemAdapter $disk): string => $rename->handle($type, $disk, $request->validated('path'), $request->validated('name'), $request->user()));

        return back();
    }

    public function destroy(StorageItemRequest $request, StorageType $type, RunOnStorage $run, DeleteStorageItem $delete): RedirectResponse
    {
        $run->handle($type, fn (FilesystemAdapter $disk) => $delete->handle($type, $disk, $request->path(), $request->user()));

        return back();
    }
}
