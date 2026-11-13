<?php

namespace App\Http\Controllers\Storage;

use App\Actions\Storage\DescribeStorages;
use App\Actions\Storage\ResetStorageSetting;
use App\Actions\Storage\SaveStorageSetting;
use App\Enums\StorageType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Storage\SaveStorageRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

class StorageController extends Controller
{
    public function index(DescribeStorages $describe): Response
    {
        return Inertia::render('storage', ['storages' => $describe->handle()]);
    }

    public function update(SaveStorageRequest $request, StorageType $type, SaveStorageSetting $save): RedirectResponse
    {
        $save->handle($type, $request->safe()->all());

        return back();
    }

    public function destroy(StorageType $type, ResetStorageSetting $reset): RedirectResponse
    {
        $reset->handle($type);

        return back();
    }
}
