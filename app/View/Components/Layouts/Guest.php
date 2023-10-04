<?php

namespace App\View\Components\Layouts;

use Illuminate\View\View;
use Illuminate\View\Component;

class Guest extends Component
{
    /**
     * Get the view / contents that represents the component.
     */
    public function render(): View
    {
        return view('layouts.guest');
    }
}
