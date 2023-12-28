<?php

namespace App\Console\Commands;

use App\Models\Document;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class CleanDocuments extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gsis:clean-documents';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Supprime les fichiers qui ne sont plus affectés à un object';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        if( Schema::hasTable('documents') ) {
            $documents = Document::whereNotNull('filename')->pluck('filename')->toArray();
        }else{
            $documents = [];
        }

        $cpt = 0;

        foreach (Storage::directories('docs') as $dir) {

            foreach (Storage::files($dir) as $file) {

                if (! in_array(preg_replace('/\/?docs\/[0-9]+\//', '', $file), $documents)) {

                    Storage::delete($file);

                    $cpt++;

                }

            }
        }

        $this->info('Suppression de '.$cpt.' fichier(s) non associé(s).');
    }
}
