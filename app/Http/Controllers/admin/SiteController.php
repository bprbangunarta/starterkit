<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Site;
use Illuminate\Http\Request;

class SiteController extends Controller
{
    public function index()
    {
        $site = Site::find(1);
        return view('admin.site.index', [
            'site' => $site,
        ]);
    }

    public function update(Request $request)
    {
        $request->validate([
            'app_name'          => 'required|string|max:255',
            'meta_description'  => 'required|string',
            'meta_keyword'      => 'required|string',
            'footer_left'       => 'required|string',
            'footer_right'      => 'required|string',
            'meta_image'        => 'image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'logo'              => 'image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
            'favicon'           => 'image|mimes:png,ico,webp|max:2048',
        ]);

        $data = Site::findOrFail($request->input('id'));

        $data->app_name         = $request->input('app_name');
        $data->meta_description = $request->input('meta_description');
        $data->meta_keyword     = $request->input('meta_keyword');
        $data->footer_left      = $request->input('footer_left');
        $data->footer_right     = $request->input('footer_right');

        if ($request->hasFile('meta_image')) {
            $file = $request->file('meta_image');
            $extension = $file->getClientOriginalExtension();
            $imageName = 'meta_image.' . $extension;

            $imagePath = $file->storeAs('site', $imageName);
            $data->meta_image = $imagePath;
        }

        if ($request->hasFile('logo')) {
            $file = $request->file('logo');
            $extension = $file->getClientOriginalExtension();
            $imageName = 'logo.' . $extension;

            $imagePath = $file->storeAs('site', $imageName);
            $data->logo = $imagePath;
        }

        if ($request->hasFile('favicon')) {
            $file = $request->file('favicon');
            $extension = $file->getClientOriginalExtension();
            $imageName = 'favicon.' . $extension;

            $imagePath = $file->storeAs('site', $imageName);
            $data->favicon = $imagePath;
        }

        $data->save();
        return redirect()->route('site.index')->with('success', 'Site Configuration updated.');
    }
}
