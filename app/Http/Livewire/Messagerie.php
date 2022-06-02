<?php

namespace App\Http\Livewire;

use Livewire\Component;

class Messagerie extends Component
{
    public $posts; // Previous posts on the object

    public $newpost; // New post content

    public function render()
    {
        return view('livewire.messagerie');
    }
}
