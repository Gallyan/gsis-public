<?php

namespace App\Http\Livewire;

use App\Models\User;
use Livewire\Component;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Validator;

class Profile extends Component
{
    use WithFileUploads;

    public User $user;
    public $upload; // Store avatar temporary upload
    public $modified = false; // True if form is modified and need to be saved

    protected function rules()
    {
        return [
            'user.firstname' => 'required|max:255',
            'user.name' => 'required|max:255',
            'user.birthday' => 'required|date',
            'user.email' => 'required|max:255|email:rfc'.((App::environment('production'))?',dns,spoof':'').'|unique:App\Models\User,email,'.$this->user->id,
            'user.employer' => 'nullable|string',
            'user.hom_adr' => 'nullable|string',
            'user.hom_zip' => 'nullable|string',
            'user.hom_cit' => 'nullable|string',
            'user.pro_ins' => 'nullable|string',
            'user.pro_adr' => 'nullable|string',
            'user.pro_zip' => 'nullable|string',
            'user.pro_cit' => 'nullable|string',
            'user.phone' => 'sometimes|phone',
            'upload' => 'nullable|image|max:1000',
        ];
    }

    public function mount() { $this->user = auth()->user(); }

    public function init() {
        $this->user = auth()->user();
        $this->reset(['upload','modified']);
        $this->dispatchBrowserEvent('pondReset');
        $this->resetValidation();
    }

    public function updated($propertyName)
    {
        $this->modified = !empty($this->user->getDirty()) || $this->upload;
        $this->validateOnly($propertyName);
    }

    public function render()
    {
        return view('livewire.profile')
            ->layoutData(['pageTitle' => __('Profile')   ]);
    }

    public function save()
    {
        $this->withValidator(function (Validator $validator) {
            if ($validator->fails()) {
                $this->emitSelf('notify-error');
            }
        })->validate();

        $this->user->save();

        if ( $this->upload ) {

            $old_avatar = $this->user->avatar;

            $this->user->update([
                'avatar' => $this->upload->store('/', 'avatars'),
            ]);

            Storage::disk('avatars')->delete($old_avatar);

            $this->dispatchBrowserEvent('pondReset');
        }

        $this->reset(['modified']);

        $this->emitSelf('notify-saved');
    }
}
