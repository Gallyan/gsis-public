<?php

namespace App\Http\Controllers\Auth;

use Illuminate\View\View;
use App\Http\Controllers\Controller;

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
