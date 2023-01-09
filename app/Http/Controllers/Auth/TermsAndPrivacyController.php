<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;

class TermsAndPrivacyController extends Controller
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

    /**
     * Display the privacy policy view.
     *
     * @return \Illuminate\View\View
     */
    public function privacy()
    {
        return view('auth.privacy-policy')
            ->layoutData(['pageTitle' => 'Politique de confidentialité']);
    }
}
