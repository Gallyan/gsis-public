<?php

namespace App\Http\Livewire;

use App\Models\Post;
use Livewire\Component;

class Messagerie extends Component
{
    public $object; // Parent object

    public $body = ''; // New content

    protected function rules() { return [
        'body' => 'required|string|max:5000',
    ]; }

    protected $listeners = ['refreshMessages' => '$refresh'];

    public function updated($propertyName) {
        $this->validateOnly($propertyName);
    }

    public function save() {
        $this->validate();

        Post::create([
            'user_id'       => Auth()->id(),
            'postable_id'   => $this->object->id,
            'postable_type' => get_class($this->object),
            'body'          => $this->body,
            'read_at'       => Auth()->id() === $this->object->user_id ? now() : null,
        ]);

        $this->reset(['body']);
        $this->emit('refreshMessages');
    }

    public function render()
    {
        return view('livewire.messagerie');
    }
}
