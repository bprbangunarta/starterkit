<?php

namespace App\Http\Controllers\Storage;

use App\Actions\Storage\RunOnStorage;
use App\Actions\StorageFiles\CreateStorageFolder;
use App\Enums\StorageType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Storage\CreateStorageFolderRequest;
use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\RedirectResponse;

class StorageFolderController extends Controller
{
    public function store(CreateStorageFolderRequest $request, StorageType $type, RunOnStorage $run, CreateStorageFolder $create): RedirectResponse
    {
        $run->handle($type, fn (FilesystemAdapter $disk): string => $create->handle($type, $disk, (string) $request->validated('path'), $request->validated('name'), $request->user()));

        return back();
    }
}
