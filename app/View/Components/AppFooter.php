<?php

namespace App\View\Components;

use App\Models\Site;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\View\Component;

class AppFooter extends Component
{
    /**
     * Create a new component instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the view / contents that represent the component.
     */
    public function render(): View|Closure|string
    {
        $site = Site::find(1);
        return view('components.app-footer', [
            'site' => $site,
        ]);
    }
}
