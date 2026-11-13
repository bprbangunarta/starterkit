<?php

namespace App\Http\Controllers\Settings;

use App\Actions\Settings\RemoveBrandImage;
use App\Actions\Settings\ReplaceBrandImage;
use App\Enums\SettingKey;
use App\Http\Controllers\Controller;
use App\Http\Requests\Settings\StoreBrandImageRequest;
use Illuminate\Http\RedirectResponse;

class SettingImageController extends Controller
{
    public function store(StoreBrandImageRequest $request, SettingKey $kind, ReplaceBrandImage $replace): RedirectResponse
    {
        $replace->handle($kind, $request->file('file'));

        return back();
    }

    public function destroy(SettingKey $kind, RemoveBrandImage $remove): RedirectResponse
    {
        $remove->handle($kind);

        return back();
    }
}
