<?php

namespace App\Http\Controllers\Storage;

use App\Actions\Storage\RenameStorageSetting;
use App\Enums\StorageType;
use App\Http\Controllers\Controller;
use App\Http\Requests\Storage\RenameStorageSettingRequest;
use Illuminate\Http\RedirectResponse;

class StorageNameController extends Controller
{
    public function update(RenameStorageSettingRequest $request, StorageType $type, RenameStorageSetting $rename): RedirectResponse
    {
        $rename->handle($type, $request->validated('name'));

        return back();
    }
}
