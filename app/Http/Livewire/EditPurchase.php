<?php

namespace App\Http\Livewire;

use Str;
use App\Models\User;
use App\Models\Purchase;
use App\Models\Manager;
use App\Models\Reception;
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
    public $showInformationMessage = false;

    // For Modal Misc editing
    public $showModal = false;
    public $subject = null;
    public $supplier = null;
    public $date = null;
    public $miscamount = null;
    public $currency = null;
    public int $misc_id;

    // For Modal Reception
    public $showReception = false; // Declenche le modal
    public $purchase_receptions; // Toutes les receptions en cours d'edition
    public int $rcpt_index; // Index in purchase_receptions en cours d'édition
    public $rcpt_subject = null;
    public $rcpt_number = null;
    public $rcpt_supplier = null;
    public $rcpt_date = null;
    public $rcpt_amount = null;
    public $rcpt_currency = null;
    public $rcpt_guests = [];
    public $del_receptions = [];

    // For Modal Guest editing
    public $showGuest = false;
    public $guest_lastname = null;
    public $guest_firstname = null;
    public $guest_establishment = null;
    public $guest_rcpt_index = null;
    public $guest_id = null;

    // For Guest list modal
    public $showList = false;
    public $list = [];

    protected function rules() { return [
        'purchase.user_id'        => 'required|exists:users,id',
        'purchase.subject'        => 'required|string|max:255',
        'purchase.institution_id' => 'required|exists:institutions,id',
        'purchase.wp'             => 'nullable|in:'.collect(Purchase::WP)->keys()->implode(','),
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

    protected function rcpt_rules() { return [
        'rcpt_subject'  => 'nullable|string',
        'rcpt_number'   => 'nullable|integer|min:0',
        'rcpt_supplier' => 'nullable|string',
        'rcpt_date'     => 'nullable|date',
        'rcpt_amount'   => 'nullable|float',
        'rcpt_currency' => 'nullable|string',
        'rcpt_guests'   => 'sometimes|array',
    ]; }

    protected function guest_rules() { return [
        'guest_lastname'      => 'nullable|string',
        'guest_firstname'     => 'nullable|string',
        'guest_establishment' => 'nullable|string',
    ]; }

    protected function list_rules() {
        return [
            'list.file' => 'required|file',
            'list.name' => 'required|string'
        ];
    }

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

        $this->purchase_receptions = $this->purchase->receptions->toArray();
        foreach( $this->purchase_receptions as $k => $rcpt ) {
            $this->purchase_receptions[ $k ]['doc'] = null;
            if ( isset($rcpt['id']) ) {
                $guestslist = Reception::findOrFail( $rcpt['id'] )->guestslist;
                if ( !is_null(  $guestslist ) ) {
                    $this->purchase_receptions[ $k ]['doc'] = $guestslist->toArray();
                }
            }
        }

        $this->reset(['uploads','modified','del_docs','del_receptions']);
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
        $this->subject = $this->supplier = $this->date = $this->miscamount = $this->currency = ''; // Reset form
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

    public function close_reception()
    {
        $this->showReception = false;

        $this->rcpt_subject = $this->rcpt_number = $this->rcpt_supplier = $this->rcpt_date = $this->rcpt_amount = $this->rcpt_currency = null;
        $this->rcpt_guests = []; // Reset form

        unset($this->rcpt_index);
    }

    public function edit_reception( int $index )
    {
        if ( $this->disabled === true ) return;

        $this->rcpt_index = $index;

        $this->rcpt_subject  = $this->purchase_receptions[$index]['subject'];
        $this->rcpt_number   = $this->purchase_receptions[$index]['number'];
        $this->rcpt_supplier = $this->purchase_receptions[$index]['supplier'];
        $this->rcpt_date     = $this->purchase_receptions[$index]['date'];
        $this->rcpt_amount   = $this->purchase_receptions[$index]['amount'];
        $this->rcpt_currency = $this->purchase_receptions[$index]['currency'];
        $this->rcpt_guests   = $this->purchase_receptions[$index]['guests'];

        $this->showReception = true;
    }

    public function del_reception( int $index ) {
        if ( $this->disabled === true ) return;

        // Stockage temporaire pour suppression lors de la sauvegarde de l'achat
        if ( isset($this->purchase_receptions[$index]['id']) )
            $this->del_receptions[] = $this->purchase_receptions[$index]['id'];

        // Suppression de la liste des achats en cours d'édition
        unset($this->purchase_receptions[$index]);
        $this->purchase_receptions = array_values( $this->purchase_receptions );

        $this->modified = true;
    }

    // Ajoute ou met à jour une réception
    public function add_reception() {
        $this->validate( $this->rcpt_rules() );

        // Pour une création on prend l'index immédiatement supérieur
        if ( !isset($this->rcpt_index) ) $this->rcpt_index = count($this->purchase_receptions);

        $this->purchase_receptions = array_replace_recursive(
            $this->purchase_receptions,
            [ $this->rcpt_index => [
                    'subject'  => $this->rcpt_subject,
                    'number'   => $this->rcpt_number,
                    'supplier' => $this->rcpt_supplier,
                    'date'     => $this->rcpt_date,
                    'amount'   => $this->rcpt_amount,
                    'currency' => $this->rcpt_currency,
                    'guests'   => $this->rcpt_guests,
                ]
            ]
        );

        $this->modified = true;

        $this->close_reception();
    }

    // Gestion des invités
    public function show_guest( $rcpt_index ) {
        $this->guest_rcpt_index = $rcpt_index;
        $this->showGuest = true;
    }

    public function close_guest() {
        $this->showGuest = false;
        $this->guest_lastname = $this->guest_firstname = $this->guest_establishment = ''; // Reset form
        $this->guest_id = $this->guest_rcpt_index = null;
    }

    public function edit_guest( int $rcpt_index, int $guest_id ) {
        if ( $this->disabled === true ) return;

        $guests = $this->purchase_receptions[$rcpt_index]['guests'];

        $this->guest_rcpt_index = $rcpt_index;

        if( $guest_id < 0 || $guest_id >= count($guests)) return;

        $this->guest_id = $guest_id;
        $this->guest_lastname      = $guests[ $guest_id ]['guest_lastname'] ?? '';
        $this->guest_firstname     = $guests[ $guest_id ]['guest_firstname'] ?? '';
        $this->guest_establishment = $guests[ $guest_id ]['guest_establishment'] ?? '';

        $this->showGuest = true;
    }

    public function del_guest( int $rcpt_index, int $guest_id ) {
        if ( $this->disabled === true ) return;

        $guests = $this->purchase_receptions[$rcpt_index]['guests'];

        if( $guest_id < 0 || $guest_id >= count($guests)) return;

        if(isset($guests[$guest_id])) unset( $guests[$guest_id] );

        $this->purchase_receptions[$rcpt_index]['guests'] = array_values( $guests );

        $this->modified = true;
    }

    // Ajoute un invité à la liste json des invités
    public function add_guest() {
        $current_guest = $this->validate( $this->guest_rules() );

        $guests = $this->purchase_receptions[ $this->guest_rcpt_index ]['guests'];

        if( isset($this->guest_id) && !empty( $this->guest_id ) ) {
            $guests[ $this->guest_id ] = $current_guest;
        } else {
            $guests[] = $current_guest;
        }

        $this->purchase_receptions[ $this->guest_rcpt_index ]['guests'] = $guests;

        $this->modified = true;

        $this->close_guest();
    }

    // Gestion des fichier de listes d'invités
    public function show_list( $rcpt_index ) {

        $this->guest_rcpt_index = $rcpt_index;
        $this->showList = true;
    }

    /* Initialisation du nom après l'upload de document */
    public function updatedListFile() {
        // Apres l'upload initialiser le nom du fichier
        if ( isset($this->list['file']) && ( !isset($this->list['name']) || empty($this->list['name']) ) ) {
            $this->list['name'] =
                Str::slug(
                    pathinfo(
                        Document::filter_filename( $this->list['file']->getClientOriginalName() ),
                    PATHINFO_FILENAME
                )
            );
        }
    }

    public function save_list() {
        $this->withValidator(function (Validator $validator) {
            if ($validator->fails()) {
                $this->emitSelf('dialog-error');
            }
        })->validate( $this->list_rules() );

        $this->purchase_receptions[ $this->guest_rcpt_index ]['list'] = $this->list;

        $this->modified = true;
        $this->emit('refreshPurchase');
        $this->close_list();
    }

    public function del_list( $rcpt_index ) {
        if ( $this->disabled === true ) return;

        if ( ! isset($this->purchase_receptions[ $rcpt_index ]) ) return;

        if ( isset($this->purchase_receptions[ $rcpt_index ]['list']) ) {
            unset($this->purchase_receptions[ $rcpt_index ]['list']);

        } elseif ( isset($this->purchase_receptions[ $rcpt_index ]['doc']) ) {
            $this->del_docs[] = $this->purchase_receptions[ $rcpt_index ]['doc']['id'];
            unset($this->purchase_receptions[ $rcpt_index ]['doc']);

            $this->modified = true;
        }
    }

    public function close_list() {
        $this->reset(['list','showList','guest_rcpt_index']);
        $this->dispatchBrowserEvent('guestlistReset');
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

        }elseif( in_array( $propertyName, array_keys($this->rcpt_rules()) ) ) {
                $this->validateOnly($propertyName, $this->rcpt_rules());

        }else if( explode(".",$propertyName)[0] === "list") {
            $this->validateOnly($propertyName, $this->list_rules());

        } else {
            $this->validateOnly($propertyName);
            if ( !in_array($propertyName, ['showModal','showReception','showGuest','showList']) )
                $this->modified = !empty($this->purchase->getDirty()) ;
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
        $creation = is_null( $this->purchase->id );

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
        foreach( array_unique($this->del_docs) as $id ) {

            Document::findOrFail( $id )->delete() ;

        }

        // Traitement des achats
        // Suppression
        foreach( $this->del_receptions as $rcpt_id ) {
            Reception::findOrFail( $rcpt_id )->delete();
        }
        // Modification & Creation
        foreach( $this->purchase_receptions as $k=>$rcpt ) {
            if ( isset($rcpt['id']) ) {
                Reception::where('id',$rcpt['id'])->update(
                    array_filter($rcpt, function($k) {
                    return !in_array( $k, ['created_at', 'updated_at', 'id', 'list', 'doc'] );
                }, ARRAY_FILTER_USE_KEY));
            } else {
                $rcpt['id'] = Reception::create(
                    array_filter(
                        array_merge(
                            $rcpt,
                            [ 'purchase_id' => $this->purchase->id ]
                        ), function($k) {
                            return !in_array( $k, ['created_at', 'updated_at', 'id', 'list', 'doc'] );
                        }, ARRAY_FILTER_USE_KEY
                    )
                )->id;
            }

            // Sauvegarde des guestlist ajoutées
            if ( isset($rcpt['list']['file']) ) {
                // Create user documents directory if not exists
                $path = 'docs/'.$this->purchase->user_id.'/';
                Storage::makeDirectory( $path );

                // Store file in directory
                $filename = $rcpt['list']['file']->storeAs( '/'.$path, $rcpt['list']['file']->hashName() );

                // Create file in BDD
                Document::create([
                    "name" => Document::filter_filename( $rcpt['list']['file']->getClientOriginalName() ),
                    "type" => 'guestlist',
                    "size" => Storage::size( $filename ),
                    "filename" => $rcpt['list']['file']->hashName(),
                    "user_id" => $this->purchase->user_id,
                    "documentable_id" => $rcpt['id'],
                    "documentable_type" => Reception::class,
                ]);
            }
            $this->dispatchBrowserEvent('guestlistReset');
        }

        $this->reset(['uploads','modified','del_docs','del_receptions']);
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

        if ( $creation ) {
            // Redirection pour modifier l'url
            return redirect()->route('edit-purchase',$this->purchase->id);
        }else{
            $this->init();
        }
    }
}
