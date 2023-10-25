<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Order;
use App\Models\Expense;
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

    protected function zip($object) {

        if(count($object->documents)===0) abort(204);

        $zip_filename = substr(strrchr($object::class, "\\"), 1)
                    .'_'
                    .$object->id
                    .'_documents.zip';

        // Initializing PHP class
        $zip = new \ZipArchive();
        $zip->open(public_path($zip_filename)   , \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

        foreach( $object->documents as $doc )
        {
            // Check rights : Owner or manager
            if (auth()->id() !== $doc->user_id && ! auth()->user()->can('manage-users')) {
                abort(403);
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
                $zip->addFile(storage_path('/app'.$pathToFile),$filename);
            }
        }

        $zip->close();

        return $zip_filename;
    }

    /* Download all quotations of an order */
    public function order(int $id) {

        return response()
            ->download( $this->zip( Order::findOrFail($id) ) )
            ->deleteFileAfterSend(true);

    }

    /* Download all documents of an expense */
    public function expense(int $id) {

        return response()
            ->download( $this->zip( Expense::findOrFail($id) ) )
            ->deleteFileAfterSend(true);

    }
}
