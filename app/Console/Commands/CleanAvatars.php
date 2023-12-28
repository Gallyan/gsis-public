<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;

class CleanAvatars extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gsis:clean-avatars';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Supprime les avatars qui ne sont plus affectés à un utilisateur';

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
     * Avatars list
     */
    protected $avatars = [];

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        if ( Schema::hasTable('users') ) {
            $this->avatars = User::whereNotNull('avatar')->pluck('avatar')->toArray();
        }

        $files = Storage::files('avatars');

        $to_delete = array_values(
            array_filter(
                $files, function ($file) {
                    return $file[8] !== '.' && ! in_array(substr($file, 8), $this->avatars);
                }
            )
        );

        Storage::delete($to_delete);

        $this->info('Suppression de '.count($to_delete).' avatar(s) non affecté(s).');
    }
}
