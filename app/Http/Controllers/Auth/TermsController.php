<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\View\View;

class TermsController extends Controller
{
    /**
     * Display the terms of use view.
     */
    public function terms(): View
    {
        return view('auth.terms');
    }
}
