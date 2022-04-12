<?php

namespace App\Http\Controllers;

use Str;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller
{
    public function download($id) {
        $doc = Document::findOrFail($id);

        // Check rights : Owner or manager
        if ( auth()->user()->id !== $doc->user_id && ! auth()->user()->can('manage-users') )
            return abort(403);

        // Create filename
        $pathToFile = '/docs/' . $doc->user_id . '/' . $doc->filename;
        if (empty($doc->name)) {
            $filename = $doc->user_id . '-' . $doc->type . '-' . $doc->filename;
        } else {
            $extension = pathinfo( $pathToFile, PATHINFO_EXTENSION);
            $filename = $doc->user_id . '-' . Str::slug( $doc->name ) . '.' . $extension;
        }

        // Check if file exist and download
        if ( Storage::exists( $pathToFile ) ) {
            return Storage::download($pathToFile, $filename);
        }

        return abort(404);
    }
}
