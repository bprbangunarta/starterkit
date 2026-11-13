<?php

namespace App\Http\Controllers\Storage;

use App\Actions\Storage\TestStorageConnection;
use App\Enums\StorageType;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StorageTestController extends Controller
{
    /** Tests the connection in force; the outcome is saved beside the settings and shown by the list. */
    public function store(Request $request, StorageType $type, TestStorageConnection $test): RedirectResponse
    {
        $test->handle($type, $request->user());

        return back();
    }
}
