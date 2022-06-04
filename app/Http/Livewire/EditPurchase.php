<?php

namespace App\Http\Livewire;

use App\Models\User;
use App\Models\Purchase;
use App\Models\Manager;
use App\Mail\PurchaseStatusChange;
use Livewire\Component;
use App\Models\Document;
use Livewire\WithFileUploads;
use Illuminate\Validation\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class EditPurchase extends Component
{
    use WithFileUploads;
    use AuthorizesRequests;

    public Purchase $purchase;
    public $uploads = [];
    public $del_docs = [];
    public $modified = false; // True if form is modified and need to be saved
    public $disabled = false; // True if user can't modify current editing Object
    public $disabledStatuses = []; // List of disabled status

    // For Modal Misc editing
    public $showModal = false;
    public $showInformationMessage = false;
    public string $subject = '';
    public string $supplier = '';
    public string $date = '';
    public string $miscamount = '';
    public string $currency = '';
    public int $misc_id;

    protected function rules() { return [
        'purchase.user_id'        => 'required|exists:users,id',
        'purchase.subject'        => 'required|string|max:255',
        'purchase.institution_id' => 'required|exists:institutions,id',
        'purchase.wp'             => 'required|in:'.collect(Purchase::WP)->keys()->implode(','),
        'uploads'                 => 'nullable|array',
        'uploads.*'               => 'mimes:xls,xlsx,doc,docx,pdf,zip,jpg,png,gif,bmp,webp,svg|max:10240',
        'purchase.miscs'          => 'sometimes|array',
        'purchase.comments'       => 'nullable|string',
        'purchase.status'         => 'required|in:'.collect(Purchase::STATUSES)->keys()->implode(','),
        'purchase.amount'         => 'nullable|float',
    ]; }

    protected function misc_rules() { return [
        'subject'    => 'required|string',
        'supplier'   => 'required|string',
        'date'       => 'required|date_format:Y-m-d',
        'miscamount' => 'required|float',
        'currency'   => 'required|string',
    ]; }

    protected function messages() { return [
        'uploads.*.image' => __('The :filename file must be an image.'),
        'uploads.*.max' => __('The size of the :filename file cannot exceed :max kilobytes.'),
        'uploads.*.mimes' => __('The file :filename must be a file of type: :values.'),
        'uploads.*.mimetypes' => __('The file :filename must be a file of type: :values.'),
    ];}

    protected $listeners = ['refreshPurchase' => '$refresh'];

    public function mount( $id = null ) {
        if ( is_null($id) ) {
            $this->purchase = $this->makeBlankPurchase();
        } else {
            $this->purchase = Purchase::findOrFail($id);

            if ( ! auth()->user()->can('manage-users') && auth()->id() !== $this->purchase->user_id )
                abort(403);
        }

        $this->reset(['uploads','modified','del_docs']);
        $this->dispatchBrowserEvent('pondReset');
        $this->resetValidation();
        $this->statesUpdate();
    }

    public function init() {
        $this->mount( $this->purchase->id );
    }

    public function statesUpdate() {
        if ( $this->purchase->status === 'cancelled' ) {
            // Commande annulée, personne ne peut plus rien faire
            $this->disabled = true;
            $this->disabledStatuses = array_keys(Purchase::STATUSES);

        } elseif ( auth()->user()->can('manage-users') ) {
            // Gestionnnaire
            if ( $this->purchase->user_id === auth()->id() ) {
                // Le gestionnaire est aussi l'auteur de la commande

                if ( $this->purchase->status === 'draft' ) {
                    $this->disabled = false;
                    $this->disabledStatuses = array_diff( array_keys( Purchase::STATUSES ), [ 'draft', 'on-hold', 'cancelled' ] );
                } elseif ( $this->purchase->status === 'on-hold' ) {

                    $this->disabled = false;
                    if ( $this->purchase->managers->doesntContain( 'user_id', auth()->id() ) ) {
                        $this->disabledStatuses = array_diff( array_keys( Purchase::STATUSES ), [ 'draft', 'on-hold', 'cancelled' ] );
                    } else {
                        $this->disabledStatuses = [];
                    }

                } elseif ( $this->purchase->status === 'in-progress' ) {
                    if ( $this->purchase->managers->doesntContain( 'user_id', auth()->id() ) ) {
                        $this->disabled = true;
                        $this->disabledStatuses = array_keys(Purchase::STATUSES);

                    } else {
                        $this->disabled = false;
                        $this->disabledStatuses = [ 'draft', 'on-hold' ];
                    }

                } elseif ( $this->purchase->status === 'processed' ) {
                    if ( $this->purchase->managers->doesntContain( 'user_id', auth()->id() ) ) {
                        $this->disabled = true;
                        $this->disabledStatuses = array_keys( Purchase::STATUSES );

                    } else {
                        $this->disabled = false;
                        $this->disabledStatuses = array_keys(Purchase::STATUSES);
                    }
                }

            } else {
                // Le gestionnaire n'est pas l'auteur de la commande

                if ( $this->purchase->managers->doesntContain( 'user_id', auth()->id() ) ) {
                    // Le gestionnaire n'est pas associé à la commande, il ne peut rien faire sans s'associer
                    $this->disabled = true;
                    $this->disabledStatuses = array_keys(Purchase::STATUSES);

                } elseif ( $this->purchase->status === 'draft' ) {
                    $this->disabled = true;
                    $this->disabledStatuses = array_keys(Purchase::STATUSES);

                } elseif ( $this->purchase->status === 'on-hold' ) {
                    $this->disabled = false;
                    $this->disabledStatuses = [ 'draft' ];

                } elseif ( $this->purchase->status === 'in-progress' ) {
                    $this->disabled = false;
                    $this->disabledStatuses = [ 'draft', 'on-hold' ];

                } elseif ( $this->purchase->status === 'processed' ) {
                    // Une commande terminée ne peut plus changer de status
                    $this->disabled = false;
                    $this->disabledStatuses = array_keys(Purchase::STATUSES);
                }
            }
        } else {
            // Utilisateur
            if ( in_array( $this->purchase->status, [ 'draft', 'on-hold' ] ) ) {
                $this->disabled = false;
                $this->disabledStatuses = array_diff( array_keys( Purchase::STATUSES ), [ 'draft', 'on-hold', 'cancelled' ] );
            } else {
                // Une fois la commande soumise, l'utilisateur ne peut plus rien faire
                $this->disabled = true;
                $this->disabledStatuses = array_keys( Purchase::STATUSES );
            }
        }
    }

    public function render()
    {
        return view('livewire.edit-purchase')
            ->layoutData(['pageTitle' => __('Non-mission purchase').' '.$this->purchase->id ]);
    }

    public function close_modal() {
        $this->showModal = false;
        $this->subject = $this->supplier = $this->date = $this->amount = $this->currency = ''; // Reset form
        unset($this->misc_id);
    }

    public function edit_misc( int $id ) {
        if ( $this->disabled === true ) return;

        $miscs = $this->purchase->miscs;

        if( $id < 1 || $id > count($miscs)) return;

        $this->misc_id = $id;
        $this->subject    = isset( $miscs[ $id-1 ]['subject'] ) ? $miscs[ $id-1 ]['subject'] : '';
        $this->supplier   = isset( $miscs[ $id-1 ]['supplier'] ) ? $miscs[ $id-1 ]['supplier'] : '';
        $this->date       = isset( $miscs[ $id-1 ]['date'] ) ? $miscs[ $id-1 ]['date'] : '';
        $this->miscamount = isset( $miscs[ $id-1 ]['miscamount'] ) ? $miscs[ $id-1 ]['miscamount'] : '';
        $this->currency   = isset( $miscs[ $id-1 ]['currency'] ) ? $miscs[ $id-1 ]['currency'] : '';

        $this->showModal = true;
    }

    public function del_misc( int $id ) {
        if ( $this->disabled === true ) return;

        $miscs = $this->purchase->miscs;
        if( $id < 1 || $id > count($miscs)) return;
        if(isset($miscs[$id-1])) unset( $miscs[$id-1] );
        $this->purchase->miscs = array_values( $miscs );
        $this->modified = true;
    }

    // Ajoute un achat à la liste json des achats
    public function add_misc() {
        $current_misc = $this->validate( $this->misc_rules() );

        $miscs = $this->purchase->miscs;
        if( !empty( $this->misc_id ) ) {
            $miscs[ $this->misc_id - 1 ] = $current_misc;
        } else {
            $miscs[] = $current_misc;
        }

        $this->purchase->miscs = $miscs;

        $this->modified = true;

        $this->close_modal();
    }

    // Ajoute un document à la liste des documents à supprimer
    public function del_doc( $id ) {
        if ( $this->disabled === true ) return;

        if( !empty( Document::find( $id ) ) && !in_array( $id, $this->del_docs ) ) {

            $this->del_docs[] = $id;
            $this->modified = true;

        }
    }

    // Associate current manager
    public function associate() {
        Manager::create([
            'user_id' => auth()->id(),
            'manageable_id' => $this->purchase->id,
            'manageable_type' => Purchase::class,
        ]);
        if ( $this->purchase->status === 'on-hold' ) {
            $this->purchase->update(['status'=>'in-progress']);
            $user = User::findOrFail($this->purchase->user_id);
            Mail::to( $user )->send( new PurchaseStatusChange( $this->purchase, $user->name, auth()->user()->name) );
        }
        $this->emit('refreshPurchase');
        $this->emit('refreshMessages');
        $this->init();
    }

    // Dissociate current manager if he's not the only one
    public function dissociate() {
        // Check if manager is not the only one
        if ( count($this->purchase->managers) > 1 ) {
            Manager::where('user_id','=',auth()->id())
                ->where('manageable_type','=',Purchase::class)
                ->where('manageable_id','=',$this->purchase->id)
                ->delete();
                $this->emit('refreshPurchase');
                $this->init();
            }
    }

    public function updated($propertyName) {
        if( in_array( $propertyName, array_keys($this->misc_rules()) ) ) {
            $this->validateOnly($propertyName, $this->misc_rules());
        } else {
            $this->validateOnly($propertyName);
            if ( $propertyName !== "showModal" ) $this->modified = !empty($this->purchase->getDirty()) ;
        }
    }

    public function updatedUploads() {
        if ( $this->uploads ) {

            $this->validateOnly( 'uploads.*' );

            $this->modified = true;
        }
    }

    public function makeBlankPurchase()
    {
        return Purchase::make([
            'user_id' => Auth()->id(),
            'miscs'   => [],
            'status' => 'draft',
        ]);
    }

    public function save()
    {
        $this->purchase->miscs = $this->purchase->miscs; //Force json encodage

        $this->withValidator(function (Validator $validator) {
                if ($validator->fails()) {
                    $this->emitSelf('notify-error');
                }
        })->validate();

        $this->purchase->save();

        // Sauvegarde des fichiers ajoutés
        if( !empty( $this->uploads ) ) {

            // Create user documents directory if not exists
            $path = 'docs/'.$this->purchase->user_id.'/';
            Storage::makeDirectory( $path );

            foreach( $this->uploads as $file ) {
                // Store file in directory
                $filename = $file->storeAs( '/'.$path, $file->hashName() );

                // Create file in BDD
                Document::create([
                    "name" => Document::filter_filename( $file->getClientOriginalName() ),
                    "type" => 'document',
                    "size" => Storage::size( $filename ),
                    "filename" => $file->hashName(),
                    "user_id" => $this->purchase->user_id,
                    "documentable_id" => $this->purchase->id,
                    "documentable_type" => Purchase::class,
                ]);
            }
            $this->dispatchBrowserEvent('pondReset');
        }

        // Suppression des fichiers à supprimer
        foreach( $this->del_docs as $id ) {

            $document = Document::find( $id ) ;

            if( !empty( $document ) ) {

                $filename = '/docs/' . $this->purchase->user_id . '/' . $document->filename ;

                if (Storage::exists( $filename )) {

                    Storage::delete( $filename );

                    $document->delete();
                }

            }
        }

        $this->reset(['uploads','modified','del_docs']);
        $this->emit('refreshPurchase');
        $this->emitSelf('notify-saved');
        $this->statesUpdate();

        if ( $this->purchase->status === 'draft' && auth()->user()->cannot('manage-users') ) {
            $this->showInformationMessage = 'submit-purchase';
        }

        if ( array_key_exists( 'status', $this->purchase->getChanges()) && $this->purchase->status !== 'draft') {
            // Envoi de mail lors d'un changement de status uniquement
            $user = User::findOrFail($this->purchase->user_id);
            Mail::to( $user )->send( new PurchaseStatusChange( $this->purchase, $user->name, auth()->user()->name) );
        }
    }
}
