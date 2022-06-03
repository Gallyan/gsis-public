<?php

namespace App\Http\Livewire;

use App\Models\Post;
use App\Models\Manager;
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

        if ( Auth()->id() !== $this->object->user_id ||
             in_array( Auth()->id(), $this->object->managers->pluck('user_id')->toArray() ) ) {
            // L'auteur du message n'est pas l'auteur de la commande, ou bien il est aussi gestionnaire
            // Il faut mettre à jour le read_at de la relation manager
            Manager::where('user_id','=',Auth()->id())
            ->where('manageable_type','=',get_class($this->object))
            ->where('manageable_id','=',$this->object->id)
            ->update(['read_at'=>now()]);
        };

        $this->reset(['body']);
        $this->emit('refreshMessages');
    }

    public function render()
    {
        return view('livewire.messagerie');
    }
}
