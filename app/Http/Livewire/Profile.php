<?php

namespace App\Http\Livewire;

use App\Models\User;
use Livewire\Component;
use Livewire\WithFileUploads;

class Profile extends Component
{
    use WithFileUploads;

    public User $user;
    public $upload;

    protected $rules = [
        'user.firstname' => 'max:24',
        'user.name' => 'max:24',
        'user.birthday' => 'sometimes',
        'user.phone' => 'sometimes',
        'upload' => 'nullable|image|max:1000',
    ];

    public function mount() { $this->user = auth()->user(); }

    public function save()
    {
        $this->validate();

        $this->user->save();

        $this->upload && $this->user->update([
            'avatar' => $this->upload->store('/', 'avatars'),
        ]);

        $this->emitSelf('notify-saved');
    }
}
