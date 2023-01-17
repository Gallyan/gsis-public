<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;

class TermsController extends Controller
{
    /**
     * Display the terms of use view.
     *
     * @return \Illuminate\View\View
     */
    public function terms()
    {
        return view('auth.terms');
    }
}
