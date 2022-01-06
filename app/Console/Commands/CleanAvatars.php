<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

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
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $this->info('Successfully do nothing for now.');
    }
}
