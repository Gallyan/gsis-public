<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Support\Facades\Storage;
use Str;

class DocumentController extends Controller
{
    public function download($id)
    {
        $doc = Document::findOrFail($id);

        // Check rights : Owner or manager
        if (auth()->id() !== $doc->user_id && ! auth()->user()->can('manage-users')) {
            return abort(403);
        }

        // Create filename
        $pathToFile = '/docs/'.$doc->user_id.'/'.$doc->filename;
        if (empty($doc->name)) {
            $filename = $doc->user_id.'-'.$doc->type.'-'.$doc->filename;
        } else {
            $filename = $doc->user_id.
                        '-'.
                        Str::slug(pathinfo($doc->name, PATHINFO_FILENAME)).
                        '.'.
                        pathinfo($pathToFile, PATHINFO_EXTENSION);
        }

        // Check if file exist and download
        if (Storage::exists($pathToFile)) {
            return Storage::download($pathToFile, $filename);
        }

        return abort(404);
    }
}
