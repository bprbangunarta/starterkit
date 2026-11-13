<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Inertia\Response;

class ErrorPreviewController extends Controller
{
    /** The statuses the app words (resources/js/lib/error-pages.ts); the route refuses any other. */
    public const STATUSES = [401, 403, 404, 419, 429, 500, 503];

    /** An error screen at full size, for the UI Guideline and for checking the wording. */
    public function show(int $status): Response
    {
        return Inertia::render('error-preview', ['status' => $status]);
    }
}
