<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class UnusedAvatars extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'gsis:unused-avatars';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Liste les avatars qui ne sont plus affectés à un utilisateur';

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
        $avatars = \App\Models\User::whereNotEmpty('avatar')->pluck('avatar')->toArray();

        $files = Storage::files('avatars');

        $to_delete = array_values(
            array_filter(
                $files, function ($file) use ($avatars) {
                    return $file[8] !== '.' && ! in_array(substr($file, 8), $avatars);
                }
            )
        );

        foreach ($to_delete as $file) {
            $this->info($file);
        }

        $this->info(count($to_delete).' avatar(s) non affecté(s)');
    }
}
