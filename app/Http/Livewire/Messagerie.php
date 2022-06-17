<?php

namespace App\Http\Livewire;

use App\Models\User;
use App\Models\Post;
use App\Models\Manager;
use Livewire\Component;
use App\Mail\NewMessage;
use Illuminate\Support\Facades\Mail;
use Illuminate\Database\Eloquent\Builder;

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

        if ( in_array( Auth()->id(), $this->object->managers->pluck('user_id')->toArray() ) ) {
            // Si l'auteur est gestionnaire il faut alors mettre à jour le read_at de la relation manager
            Manager::where('user_id','=',Auth()->id())
            ->where('manageable_type','=',get_class($this->object))
            ->where('manageable_id','=',$this->object->id)
            ->update(['read_at'=>now()]);
        };

        $this->reset(['body']);
        $this->emit('refreshMessages');

        $authors_id = Post::whereHasMorph(
            'postable',
            [ get_class($this->object) ],
            function (Builder $query) {
                $query->where('id', '=', $this->object->id);
            }
        )->pluck('user_id')->unique()->toArray();

        $managers_id = $this->object->managers->pluck('user_id')->toArray();

        // Liste des destinataires
        if ( empty($managers_id) ) {
            $all_managers_id = User::role('manager')->pluck('id')->toArray();
        }

        $cpt = 0;
        foreach ( array_unique( array_merge( $authors_id, $managers_id, $all_managers_id ?? [], [ $this->object->user_id ] ) ) as $dest_id ) {
            if ( Auth()->id() !== $dest_id ) {
                // On n'envoie pas de mail à l'auteur du message
                $user = User::findOrFail($dest_id);
                Mail::to( $user )->send( new NewMessage( $this->object, $user->name, auth()->user()->name) );
                $cpt++;
            }
        }
        $cpt && $this->emitSelf('notify-sent-ok');
    }

    public function render()
    {
        return view('livewire.messagerie');
    }
}
