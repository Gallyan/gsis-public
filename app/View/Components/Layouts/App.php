<?php

namespace App\View\Components\Layouts;

use Illuminate\View\Component;

class App extends Component
{
    /**
     * Create a new component instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Get the view / contents that represents the component.
     *
     * @return \Illuminate\View\View|string
     */
    public function render()
    {
        return view('layouts.app');
    }
}
