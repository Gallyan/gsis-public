<?php

namespace App\Http\Livewire;

use App\Models\Document;
use App\Models\User;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Validator;
use Livewire\Component;
use Livewire\WithFileUploads;
use Spatie\Permission\Models\Role;
use Str;

class EditUser extends Component
{
    use WithFileUploads;

    public $user_id;

    public User $user;

    public $upload; // Store avatar temporary upload

    public $modified = false; // True if form is modified and need to be saved

    public $forceemail = false;

    public $selectedroles = [];

    public $isAuthManager = false;

    /* Modal d'ajout de document */
    public $showModal = false;

    public $showDeleteModal = false;

    public $delDocName = '';

    public $doc = [
        'from' => null,
        'to' => null,
    ]; // Store uploaded document

    protected function rules()
    {
        return [
            'user.firstname' => 'required|max:255',
            'user.lastname' => 'required|max:255',
            'user.birthday' => 'required|date',
            'user.birthplace' => 'required|string',
            'user.email' => 'required|max:255|email:rfc'.((App::environment('production')) ? ',dns,spoof' : '').'|unique:App\Models\User,email,'.$this->user->id,
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
            'user.locale' => 'required|in:fr,en',
            'selectedroles' => 'sometimes|array',
            'selectedroles.*' => 'sometimes|boolean',
            'forceemail' => 'boolean',
        ];
    }

    protected function validationAttributes()
    {
        return [
            'doc.file' => __('File'),
            'doc.type' => __('Type'),
            'doc.name' => __('Name'),
            'doc.from' => __('Valid from date'),
            'doc.to' => __('Expiration date'),
        ];
    }

    protected function messages()
    {
        return [
            'doc.from.before' => __('Expiration date prior to valid from date.'),
            'doc.to.after' => __('Expiration date prior to valid from date.'),
        ];
    }

    protected function doc_rules()
    {
        return [
            'doc.id' => 'nullable|exists:documents,id',
            'doc.file' => 'requiredIf:doc.id,null|file',
            'doc.type' => 'required|string',
            'doc.name' => 'required|string',
            'doc.from' => 'nullable|date:Y-m-d'.(isset($this->doc) && $this->doc['to'] ? '|before:doc.to' : ''),
            'doc.to' => 'nullable|date:Y-m-d'.(isset($this->doc) && $this->doc['from'] ? '|after:doc.from' : ''),
        ];
    }

    protected $listeners = ['refreshUser' => '$refresh'];

    public function mount(int $id)
    {
        if (! auth()->user()->can('manage-users') && auth()->id() !== $id) {
            abort(403);
        }

        $this->isAuthManager = auth()->user()->can('manage-users');

        $this->user_id = $id;
        $this->init();
    }

    public function init()
    {
        $this->user = $this->user_id === auth()->id() ? auth()->user() : User::findOrFail($this->user_id);
        $this->selectedroles = array_fill_keys($this->user->roles->pluck('name')->toArray(), '1');
        $this->reset(['upload', 'modified','forceemail']);
        $this->dispatchBrowserEvent('pondReset');
        $this->resetValidation();
    }

    public function isRoleModified()
    {
        return
            array_fill_keys($this->user->roles->pluck('name')->toArray(), '1')
            !==
            array_filter($this->selectedroles);
    }

    public function updated($propertyName)
    {
        $this->modified = ! empty($this->user->getDirty()) || $this->upload || $this->isRoleModified() || $this->forceemail;

        if (explode('.', $propertyName)[0] === 'doc') {
            $this->validateOnly($propertyName, $this->doc_rules());
        } else {
            $this->validateOnly($propertyName);
        }
    }

    public function editDoc(int $doc_id)
    {
        $doc = Document::findOrFail($doc_id);
        $this->reset(['doc']);
        $this->doc['id'] = $doc->id;
        $this->doc['type'] = $doc->type;
        $this->doc['name'] = $doc->name;
        $this->doc['from'] = $doc->from?->format('Y-m-d');
        $this->doc['to'] = $doc->to?->format('Y-m-d');
        $this->showModal = true;
    }

    public function render()
    {
        return view(
            'livewire.edit-user', [
            'Roles' => Role::all()->sortByDesc('id')->pluck('name'),
            'languages' => [
                'fr' => __('fr', [], 'fr'),
                'en' => __('en', [], 'en'),
            ],
            ]
        )->layoutData(['pageTitle' => $this->user->name]);
    }

    public function save()
    {
        if (! auth()->user()->can('manage-users') && auth()->id() !== $this->user->id) {
            abort(403);
        }

        $this->withValidator(
            function (Validator $validator) {
                if ($validator->fails()) {
                    $this->emitSelf('notify-error');
                }
            }
        )->validate();

        /* Force email validation by admin */
        if ($this->forceemail && auth()->user()->can('manage-users') ) {
            $this->user->markEmailAsVerified();
        }

        $this->user->save();

        if ($this->upload) {

            $old_avatar = $this->user->avatar;

            $this->user->update(
                [
                'avatar' => $this->upload->storeAs(
                    '/',
                    $this->user->id.'-'.$this->upload->hashName(),
                    'avatars'
                ),
                ]
            );

            // Delete previous avatar if exists
            if (! empty($old_avatar) && Storage::disk('avatars')->exists($old_avatar)) {

                Storage::disk('avatars')->delete($old_avatar);

            }

            // Reset avatar upload form
            $this->dispatchBrowserEvent('pondReset');
        }

        if ($this->isRoleModified() && auth()->user()->can('manage-roles')) {
            foreach ($this->selectedroles as $role => $assigned) {
                if ($role !== 'admin' || auth()->user()->can('manage-admin')) {
                    if ((bool) $assigned === true && Role::findByName($role)) {
                        $this->user->assignRole($role);
                    } else {
                        $this->user->removeRole($role);
                    }
                }
            }
        }

        $this->reset(['modified','forceemail']);
        $this->emit('refreshUser');
        $this->emitSelf('notify-saved');
    }

    /* Initialisation du nom après l'upload de document */
    public function updatedDocFile()
    {
        // Apres l'upload initialiser le nom du fichier
        if (isset($this->doc['file']) && (! isset($this->doc['name']) || empty($this->doc['name']))) {
            $this->doc['name'] =
                Str::slug(
                    pathinfo(
                        Document::filter_filename($this->doc['file']->getClientOriginalName()),
                        PATHINFO_FILENAME
                    )
                );
        }
    }

    public function confirm($id)
    {
        $this->showDeleteModal = $id;
        $this->delDocName = Document::findOrFail($id)->name;
    }

    public function del_doc($id)
    {

        $document = Document::findOrFail($id);

        if (! auth()->user()->can('manage-users') && auth()->id() !== $document->user_id) {
            abort(403);
        }

        $document->delete();

        $this->emit('refreshUser');
        $this->close_modal();
    }

    public function save_doc()
    {
        $this->withValidator(
            function (Validator $validator) {
                if ($validator->fails()) {
                    $this->emitSelf('dialog-error');
                }
            }
        )->validate($this->doc_rules());

        if (isset($this->doc['id']) ) {
            // Document modification
            Document::findOrFail($this->doc['id'])
                ->update(
                    [
                    'id' => $this->doc['id'],
                    'name' => $this->doc['name'],
                    'type' => $this->doc['type'],
                    'from' => $this->doc['from'],
                    'to' => $this->doc['to'],
                    ]
                );

        } else {
            // Document creation

            // Create user documents directory if not exists
            $path = 'docs/'.$this->user->id.'/';
            Storage::makeDirectory($path);

            $filename = $this->doc['file']->storeAs(
                '/docs/'.$this->user->id.'/',
                $this->doc['file']->hashName()
            );

            Document::create(
                [
                'name' => $this->doc['name'],
                'type' => $this->doc['type'],
                'size' => Storage::size($filename),
                'from' => $this->doc['from'],
                'to' => $this->doc['to'],
                'filename' => $this->doc['file']->hashName(),
                'user_id' => $this->user->id,
                'documentable_id' => $this->user->id,
                'documentable_type' => User::class,
                ]
            );
        }

        $this->emit('refreshUser');
        $this->close_modal();
    }

    public function show_modal()
    {
        $this->reset(['doc']);
        $this->resetValidation();
        $this->showModal = true;
    }

    public function close_modal()
    {
        $this->reset(['doc', 'showModal', 'showDeleteModal', 'delDocName']);
        $this->dispatchBrowserEvent('pondReset');
    }

    public function reset_password()
    {
        $status = Password::sendResetLink(['email' => $this->user->email]);

        if ($status === Password::RESET_LINK_SENT) {
            $this->emit('notify-sent-ok');
        } else {
            $this->addError('password', $status);
            $this->emit('notify-sent-error');
        }
    }
}
