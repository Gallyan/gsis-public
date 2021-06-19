<?php

namespace App\Http\Livewire;

use App\Models\User;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\App;

class Profile extends Component
{
    use WithFileUploads;

    public User $user;
    public $upload;

    protected function rules()
    {
        return [
            'user.firstname' => 'required|max:255',
            'user.name' => 'required|max:255',
            'user.birthday' => 'sometimes|date',
            'user.email' => 'required|max:255|email:rfc'.((App::environment('production'))?',dns,spoof':'').'|unique:App\Models\User,email,'.$this->user->id,
            'user.employer' => 'sometimes|string',
            'user.phone' => 'sometimes|phone',
            'upload' => 'nullable|image|max:1000',
        ];
    }

    public function mount() { $this->user = auth()->user(); }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

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
