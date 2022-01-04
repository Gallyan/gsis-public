<?php

namespace App\Http\Livewire;

use App\Models\User;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;

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
            'user.birthday' => 'required|date',
            'user.email' => 'required|max:255|email:rfc'.((App::environment('production'))?',dns,spoof':'').'|unique:App\Models\User,email,'.$this->user->id,
            'user.employer' => 'sometimes|string',
            'user.phone' => 'sometimes|phone',
            'upload' => 'nullable|image|max:1000',
        ];
    }

    public function mount() { $this->user = auth()->user(); }

    public function init() { $this->user = auth()->user(); }

    public function updated($propertyName)
    {
        $this->validateOnly($propertyName);
    }

    public function render()
    {
        return view('livewire.profile')
            ->layoutData(['pageTitle' => __('Profile')   ]);
    }

    public function save()
    {
        $this->validate();

        $this->user->save();

        if ( $this->upload ) {

            $old_avatar = $this->user->avatar;

            $this->user->update([
                'avatar' => $this->upload->store('/', 'avatars'),
            ]);

            Storage::disk('avatars')->delete($old_avatar);
        }

        $this->emitSelf('notify-saved');
    }
}
