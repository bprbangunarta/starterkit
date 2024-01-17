<?php

namespace App\Http\Controllers;

use App\Models\Site;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $site = Site::find(1);
        return view('dashboard', [
            'site' => $site,
        ]);
    }
}
