<?php

namespace App\Http\Livewire;

use App\Mail\NewMessage;
use App\Models\Document;
use App\Models\Manager;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class Messagerie extends Component
{
    use WithFileUploads;

    public $object; // Parent object

    public $body = ''; // New content

    public $showAddFile = false; // Show filepond to add file to message

    public $uploads = [];

    protected function rules()
    {
        return [
            'body' => 'required_without:uploads|string|max:5000',
            'uploads' => 'nullable|array',
            'uploads.*' => 'mimes:xls,xlsx,doc,docx,pdf,zip,jpg,png,gif,bmp,webp,svg|max:10240',
        ];
    }

    protected function validationAttributes()
    {
        return [
            'body' => __('message'),
        ];
    }

    protected function messages()
    {
        return [
            'body.required_without' => __('A message or an attachment is required'),
            'uploads.*.image' => __('The :filename file must be an image.'),
            'uploads.*.max' => __('The size of the :filename file cannot exceed :max kilobytes.'),
            'uploads.*.mimes' => __('The file :filename must be a file of type: :values.'),
            'uploads.*.mimetypes' => __('The file :filename must be a file of type: :values.'),
        ];
    }

    protected $listeners = ['refreshMessagerie' => '$refresh'];

    public function updated($propertyName)
    {
        $this->resetValidation();
        $this->validateOnly($propertyName);
    }

    public function updatedUploads()
    {
        if ($this->uploads) {
            $this->validateOnly('uploads.*');
        }
    }

    public function save()
    {
        $this->validate();

        $post = Post::create(
            [
            'user_id' => Auth()->id(),
            'postable_id' => $this->object->id,
            'postable_type' => get_class($this->object),
            'body' => $this->body,
            'read_at' => Auth()->id() === $this->object->user_id ? now() : null,
            ]
        );

        // Sauvegarde des fichiers ajoutés
        if (! empty($this->uploads)) {

            // Create user documents directory if not exists
            $path = 'docs/'.$this->object->user_id.'/';
            Storage::makeDirectory($path);

            foreach ($this->uploads as $file) {
                // Store file in directory
                $filename = $file->storeAs('/'.$path, $file->hashName());

                // Create file in BDD
                Document::create(
                    [
                    'name' => Document::filter_filename($file->getClientOriginalName()),
                    'type' => 'attachment',
                    'size' => Storage::size($filename),
                    'filename' => $file->hashName(),
                    'user_id' => $this->object->user_id,
                    'documentable_id' => $post->id,
                    'documentable_type' => Post::class,
                    ]
                );
            }
            $this->dispatchBrowserEvent('attachmentReset');
        }

        if (is_a($this->object, \App\Models\Expense::class)) {
            $managers_id = $this->object->mission->managers->pluck('user_id')->toArray();
        } else {
            $managers_id = $this->object->managers->pluck('user_id')->toArray();
        }
        if (in_array(Auth()->id(), $managers_id)) {
            // Si l'auteur est gestionnaire il faut alors mettre à jour le read_at de la relation manager
            Manager::where('user_id', '=', Auth()->id())
                ->where('manageable_type', '=', get_class($this->object))
                ->where('manageable_id', '=', $this->object->id)
                ->update(['read_at' => now()]);
        }

        $this->reset(['body','uploads','showAddFile']);
        $this->emit('refreshMessagerie');

        // Liste des destinataires
        $authors_id = Post::whereHasMorph(
            'postable',
            [get_class($this->object)],
            function (Builder $query) {
                $query->where('id', '=', $this->object->id);
            }
        )->pluck('user_id')->unique()->toArray();

        if (empty($managers_id)) {
            $all_managers_id = User::role('manager')->pluck('id')->toArray();
        }

        $cpt = 0;
        foreach (array_unique(array_merge($authors_id, $managers_id, $all_managers_id ?? [], [$this->object->user_id])) as $dest_id) {
            if (Auth()->id() !== $dest_id) {
                // On n'envoie pas de mail à l'auteur du message
                $user = User::findOrFail($dest_id);
                Mail::to($user)->send(new NewMessage($this->object, $user->name, auth()->user()->name));
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
