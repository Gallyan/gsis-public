<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DocumentController extends Controller
{
    public function download($id) {
        return 'Download document '.$id;
    }
}
