<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Order;
use App\Models\Expense;
use App\Models\Mission;
use App\Models\Purchase;
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

    protected function zip(string $zip_filename, $documents ) {

        if(count($documents)===0) abort(204);

        $zip = new \ZipArchive();
        $zip->open(public_path($zip_filename)   , \ZipArchive::CREATE | \ZipArchive::OVERWRITE);

        foreach( $documents as $doc )
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
            ->download( $this->zip(
                'Order_'.$id.'_documents.zip',
                Order::findOrFail($id)->documents ) )
            ->deleteFileAfterSend(true);

    }

    /* Download all documents of an expense */
    public function expense(int $id) {

        return response()
            ->download( $this->zip(
                'Expense_'.$id.'_documents.zip',
                Expense::findOrFail($id)->documents ) )
            ->deleteFileAfterSend(true);

    }

    /* Download all documents of a purchase */
    public function purchase(int $id) {

        return response()
            ->download( $this->zip(
                'Purchase_'.$id.'_documents.zip',
                Purchase::findOrFail($id)->documents ) )
            ->deleteFileAfterSend(true);

    }

    /* Download all documents of a mission */
    public function mission(int $id) {

        return response()
            ->download( $this->zip(
                'Mission_'.$id.'_documents.zip',
                Mission::findOrFail($id)->documents->filter(fn ($d) => $d->type != 'programme') ) )
            ->deleteFileAfterSend(true);

    }
}
