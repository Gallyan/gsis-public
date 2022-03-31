<?php

namespace App\Http\Livewire;

use Str;
use App\Models\User;
use Livewire\Component;
use App\Models\Document;
use Livewire\WithFileUploads;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\App;
use Illuminate\Validation\Validator;
use Illuminate\Support\Facades\Storage;

class Profile extends Component
{
    use WithFileUploads;

    public User $user;
    public $upload; // Store avatar temporary upload
    public $modified = false; // True if form is modified and need to be saved
    public $selectedroles = [];

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
            'selectedroles' => 'required|array',
            'selectedroles.*' => 'sometimes|boolean',
        ];
    }

    protected function doc_rules() {
        return [
            'doc.file' => 'required|file',
            'doc.type' => 'required|string',
            'doc.name' => 'required|string'
        ];
    }

    protected $listeners = ['refreshUser' => '$refresh'];

    public function mount() { $this->init(); }

    public function init() {
        $this->user = auth()->user();
        $this->selectedroles = array_fill_keys( $this->user->roles->pluck('name')->toArray(), '1');
        $this->reset(['upload','modified']);
        $this->dispatchBrowserEvent('pondReset');
        $this->resetValidation();
    }

    public function isRoleModified() {
        return
            array_fill_keys( $this->user->roles->pluck('name')->toArray(), "1" )
            !==
            array_filter( $this->selectedroles );
    }

    public function updated($propertyName)
    {
        $this->modified = !empty($this->user->getDirty()) || $this->upload || $this->isRoleModified();

        if( explode(".",$propertyName)[0] === "doc") {
            $this->validateOnly($propertyName, $this->doc_rules());
        }else{
            $this->validateOnly($propertyName);
        }
    }

    public function render()
    {
        return view('livewire.profile',[
                'Roles' => Role::all()->sortByDesc('id')->pluck('name'),
            ])->layoutData(['pageTitle' => __('Profile')]);
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
                'avatar' => $this->upload->storeAs(
                    '/',
                    $this->user->id.'-'.$this->upload->hashName(),
                    'avatars'
                ),
            ]);

            Storage::disk('avatars')->delete($old_avatar);

            $this->dispatchBrowserEvent('pondReset');
        }

        if ( $this->isRoleModified() ) {
            foreach( $this->selectedroles as $role => $assigned ) {
                if ( (bool)$assigned === true && Role::findByName($role) ) {
                    $this->user->assignRole( $role );
                } else {
                    $this->user->removeRole( $role );
                }
            }
        }

        $this->reset(['modified']);

        $this->emitSelf('notify-saved');
    }

    /* Modal d'ajout de document */
    public $showModal = false;
    public $doc = []; // Store uploaded document

    /* Initialisation du nom après l'upload de document */
    public function updatedDocFile() {
        // Apres l'upload initialiser le nom du fichier
        if ( isset($this->doc['file']) && ( !isset($this->doc['name']) || empty($this->doc['name']) ) ) {
            $this->doc['name'] =
                Str::slug(
                    pathinfo(
                        Document::filter_filename( $this->doc['file']->getClientOriginalName() ),
                    PATHINFO_FILENAME
                )
            );
        }
    }

    public function del_doc( $id ) {

        // TODO Add access validation

        $document = Document::find( $id ) ;

        if( !empty( $document ) ) {

            $filename = '/docs/' . $this->user->id . '/' . $document->filename ;

            if (Storage::exists( $filename )) {

                Storage::delete( $filename );

                $document->delete();
            }

        }

        $this->emit('refreshUser');
    }

    public function save_doc() {
        $this->withValidator(function (Validator $validator) {
            if ($validator->fails()) {
                $this->emitSelf('dialog-error');
            }
        })->validate( $this->doc_rules() );

        // Create user documents directory if not exists
        $path = 'docs/'.$this->user->id.'/';
        Storage::makeDirectory( $path );

        $filename = $this->doc['file']->storeAs(
                        '/docs/'.$this->user->id.'/',
                        $this->doc['file']->hashName()
                    );

        Document::create([
            "name" => $this->doc['name'],
            "type" => $this->doc['type'],
            "size" => Storage::size( $filename ),
            "filename" => $this->doc['file']->hashName(),
            "user_id" => $this->user->id,
            "documentable_id" => $this->user->id,
            "documentable_type" => User::class,
        ]);

        $this->emit('refreshUser');
        $this->close_modal();
    }

    public function close_modal() {
        $this->reset(['doc','showModal']);
        $this->dispatchBrowserEvent('pondReset');
    }
}
