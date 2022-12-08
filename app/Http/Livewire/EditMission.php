<?php

namespace App\Http\Livewire;

use App\Models\User;
use App\Models\Mission;
use App\Models\Manager;
use App\Models\Institution;
use App\Mail\MissionStatusChange;
use Livewire\Component;
use App\Models\Document;
use Livewire\WithFileUploads;
use Illuminate\Validation\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Validation\Rule;

class EditMission extends Component
{
    use WithFileUploads, AuthorizesRequests;

    public Mission $mission;
    public $uploads = [];
    public $programme; // Store uploaded conference programme temporary upload
    public $del_docs = [];
    public $modified = false; // True if form is modified and need to be saved
    public $disabled = false; // True if user can't modify current editing Mission
    public $disabledStatuses = []; // List of disabled status
    public $showInformationMessage = false;
    public $showTicket = false;
    public $showExtra = false;
    public $isAuthManager = false;
    public $showWP = false;

    // Ticket
    public $ticket_id;
    public $ticket_mode;
    public $ticket_direction;
    public $ticket_number;
    public $ticket_date;
    public $ticket_time;
    public $ticket_from;
    public $ticket_to;

    // Extra costs
    public $extra_meal;
    public $extra_taxi;
    public $extra_transport;
    public $extra_personal_car;
    public $extra_rental_car;
    public $extra_parking;
    public $extra_registration;
    public $extra_others;

    protected function rules() { return [
        'mission.user_id'        => 'required|exists:users,id',
        'mission.subject'        => 'required|string|max:255',
        'mission.institution_id' => 'required|exists:institutions,id',
        'mission.om'             => 'required_if:mission.status,in-progress',
        'mission.wp'             => [
            'sometimes',
            Rule::requiredIf(fn () => Institution::find($this->mission->institution_id)->wp),
            'nullable',
            'integer',
            'min:1'
        ],
        'mission.conference'     => 'boolean',
        'mission.conf_amount'    => 'nullable|float',
        'mission.conf_currency'  => 'required_with:conf_amount|nullable|string|size:3',
        'mission.costs'          => 'boolean',
        'mission.dest_country'   => 'string|max:2|uppercase',
        'mission.dest_city'      => 'string|max:50',
        'mission.departure'      => 'required|date',
        'mission.from'           => 'boolean',
        'mission.return'         => 'required|date|after_or_equal:mission.departure',
        'mission.to'             => 'boolean',
        'mission.tickets'        => 'nullable|array',
        'mission.accomodation'   => 'boolean',
        'mission.extra'          => 'nullable|array',
        'programme'              => 'nullable|mimes:xls,xlsx,doc,docx,pdf,zip,jpg,png,gif,bmp,webp,svg|max:10240',
        'uploads'                => 'nullable|array',
        'uploads.*'              => 'mimes:xls,xlsx,doc,docx,pdf,zip,jpg,png,gif,bmp,webp,svg|max:10240',
        'mission.comments'       => 'nullable|string',
        'mission.status'         => 'required|in:'.collect(Mission::STATUSES)->keys()->implode(','),
    ]; }

    protected function ticket_rules() { return [
        'ticket_mode' => 'nullable',
        'ticket_direction' => 'nullable',
        'ticket_number' => 'nullable',
        'ticket_date' => 'nullable',
        'ticket_time' => 'nullable',
        'ticket_from' => 'nullable',
        'ticket_to' => 'nullable',
    ]; }

    protected function extra_rules() { return [
        'extra_meal' => 'boolean',
        'extra_taxi' => 'boolean',
        'extra_transport' => 'boolean',
        'extra_personal_car' => 'boolean',
        'extra_rental_car' => 'boolean',
        'extra_parking' => 'boolean',
        'extra_registration' => 'boolean',
        'extra_others' => 'nullable|string',
    ]; }

    protected function messages() { return [
        'uploads.*.image' => __('The :filename file must be an image.'),
        'uploads.*.max' => __('The size of the :filename file cannot exceed :max kilobytes.'),
        'uploads.*.mimes' => __('The file :filename must be a file of type: :values.'),
        'uploads.*.mimetypes' => __('The file :filename must be a file of type: :values.'),
        'mission.om.required_if' => __('validation.required'),
    ];}

    protected $listeners = ['refreshMission' => '$refresh'];

    public function mount( $id = null ) {
        $this->isAuthManager = auth()->user()->can('manage-users');

        if ( is_null($id) ) {
            $this->mission = $this->makeBlankMission();
        } else {
            $this->mission = Mission::findOrFail($id);
            $this->showWP = $this->mission->institution->wp;

            if ( ! auth()->user()->can('manage-users') && auth()->id() !== $this->mission->user_id )
                abort(403);
        }

        $this->reset(['uploads','modified','del_docs','programme']);
        $this->dispatchBrowserEvent('pondReset');
        $this->resetValidation();
        $this->statesUpdate();
    }

    public function init() {
        $this->mount( $this->mission->id );
    }

    public function statesUpdate() {
        if ( $this->mission->status === 'cancelled' ) {
            // Commande annulée, personne ne peut plus rien faire
            $this->disabled = true;
            $this->disabledStatuses = array_keys(Mission::STATUSES);

        } elseif ( auth()->user()->can('manage-users') ) {
            // Gestionnnaire
            if ( $this->mission->user_id === auth()->id() ) {
                // Le gestionnaire est aussi l'auteur de la commande

                if ( $this->mission->status === 'draft' ) {
                    $this->disabled = false;
                    $this->disabledStatuses = array_diff( array_keys( Mission::STATUSES ), [ 'draft', 'on-hold', 'cancelled' ] );
                } elseif ( $this->mission->status === 'on-hold' ) {

                    $this->disabled = false;
                    if ( $this->mission->managers->doesntContain( 'user_id', auth()->id() ) ) {
                        $this->disabledStatuses = array_diff( array_keys( Mission::STATUSES ), [ 'draft', 'on-hold', 'cancelled' ] );
                    } else {
                        $this->disabledStatuses = [];
                    }

                } elseif ( $this->mission->status === 'in-progress' ) {
                    if ( $this->mission->managers->doesntContain( 'user_id', auth()->id() ) ) {
                        $this->disabled = true;
                        $this->disabledStatuses = array_keys(Mission::STATUSES);

                    } else {
                        $this->disabled = false;
                        $this->disabledStatuses = [ 'draft', 'on-hold' ];
                    }

                } elseif ( $this->mission->status === 'processed' ) {
                    if ( $this->mission->managers->doesntContain( 'user_id', auth()->id() ) ) {
                        $this->disabled = true;
                        $this->disabledStatuses = array_keys( Mission::STATUSES );

                    } else {
                        $this->disabled = false;
                        $this->disabledStatuses = array_keys(Mission::STATUSES);
                    }
                }

            } else {
                // Le gestionnaire n'est pas l'auteur de la commande

                if ( $this->mission->managers->doesntContain( 'user_id', auth()->id() ) ) {
                    // Le gestionnaire n'est pas associé à la commande, il ne peut rien faire sans s'associer
                    $this->disabled = true;
                    $this->disabledStatuses = array_keys(Mission::STATUSES);

                } elseif ( $this->mission->status === 'draft' ) {
                    $this->disabled = true;
                    $this->disabledStatuses = array_keys(Mission::STATUSES);

                } elseif ( $this->mission->status === 'on-hold' ) {
                    $this->disabled = false;
                    $this->disabledStatuses = [ 'draft' ];

                } elseif ( $this->mission->status === 'in-progress' ) {
                    $this->disabled = false;
                    $this->disabledStatuses = [ 'draft', 'on-hold' ];

                } elseif ( $this->mission->status === 'processed' ) {
                    // Une commande terminée ne peut plus changer de status
                    $this->disabled = false;
                    $this->disabledStatuses = array_keys(Mission::STATUSES);
                }
            }
        } else {
            // Utilisateur
            if ( in_array( $this->mission->status, [ 'draft', 'on-hold' ] ) ) {
                $this->disabled = false;
                $this->disabledStatuses = array_diff( array_keys( Mission::STATUSES ), [ 'draft', 'on-hold', 'cancelled' ] );
            } else {
                // Une fois la commande soumise, l'utilisateur ne peut plus rien faire
                $this->disabled = true;
                $this->disabledStatuses = array_keys( Mission::STATUSES );
            }
        }
    }

    public function render()
    {
        return view('livewire.edit-mission')
            ->layoutData(['pageTitle' => __('Mission').' '.$this->mission->id ]);
    }

    // Ajoute un document à la liste des documents à supprimer
    public function del_doc( $id ) {
        if ( $this->disabled === true ) return;

        if( !empty( Document::find( $id ) ) && !in_array( $id, $this->del_docs ) ) {

            $this->del_docs[] = $id;
            $this->modified = true;

        }
    }

    // Associate current manager to Mission
    public function associate() {
        Manager::create([
            'user_id' => auth()->id(),
            'manageable_id' => $this->mission->id,
            'manageable_type' => Mission::class,
        ]);
        if ( $this->mission->status === 'on-hold' ) {
            $this->mission->update(['status'=>'in-progress']);
            $user = User::findOrFail($this->mission->user_id);
            Mail::to( $user )->send( new MissionStatusChange( $this->mission, $user->name, auth()->user()->name) );
        }
        $this->emit('refreshMission');
        $this->emit('refreshMessages');
        $this->init();
    }

    // Dissociate current manager from Mission if he's not the only one
    public function dissociate() {
        // Check if manager is not the only one
        if ( count($this->mission->managers) > 1 ) {
            Manager::where('user_id','=',auth()->id())
                ->where('manageable_type','=',Mission::class)
                ->where('manageable_id','=',$this->mission->id)
                ->delete();
                $this->emit('refreshMission');
                $this->init();
            }
    }

    public function updated($propertyName) {
        if( in_array( $propertyName, array_keys($this->extra_rules()) ) ) {
            $this->validateOnly($propertyName, $this->extra_rules());

        } elseif( in_array( $propertyName, array_keys($this->ticket_rules()) ) ) {
            $this->validateOnly($propertyName, $this->ticket_rules());

        } else {
            $this->validateOnly($propertyName);
            $this->modified = !empty($this->mission->getDirty()) ;
        }
    }

    public function updatedUploads() {
        if ( $this->uploads ) {

            $this->validateOnly( 'uploads.*' );

            $this->modified = true;
        }
    }

    public function updatedMissionInstitutionId() {
        $this->showWP = Institution::find($this->mission->institution_id)->wp;
        ! $this->showWP && $this->validateOnly('mission.wp');
    }

    // Start Tickets

    public function close_ticket() {
        // Hide modal
        $this->showTicket = false;

        // Reset form
        $this->ticket_mode      = null;
        $this->ticket_direction = null;
        $this->ticket_number    = null;
        $this->ticket_date      = null;
        $this->ticket_time      = null;
        $this->ticket_from      = null;
        $this->ticket_to        = null;
        unset($this->ticket_id);
    }

    // Affiche le modal d'édition du ticket après en avoir initialisé les valeurs
    public function edit_ticket( int $id ) {
        if ( $this->disabled === true ) return;

        // Get from json to array
        $tickets = $this->mission->tickets;

        // Control
        if( $id < 1 || $id > count($tickets)) return;

        // Initialize values
        $this->ticket_id = $id;
        $this->ticket_mode      = isset( $tickets[ $id-1 ]['ticket_mode'] ) ? $tickets[ $id-1 ]['ticket_mode'] : null;
        $this->ticket_direction = isset( $tickets[ $id-1 ]['ticket_direction'] ) ? $tickets[ $id-1 ]['ticket_direction'] : null;
        $this->ticket_number    = isset( $tickets[ $id-1 ]['ticket_number'] ) ? $tickets[ $id-1 ]['ticket_number'] : null;
        $this->ticket_date      = isset( $tickets[ $id-1 ]['ticket_date'] ) ? $tickets[ $id-1 ]['ticket_date'] : null;
        $this->ticket_time      = isset( $tickets[ $id-1 ]['ticket_time'] ) ? $tickets[ $id-1 ]['ticket_time'] : null;
        $this->ticket_from      = isset( $tickets[ $id-1 ]['ticket_from'] ) ? $tickets[ $id-1 ]['ticket_from'] : null;
        $this->ticket_to        = isset( $tickets[ $id-1 ]['ticket_to'] ) ? $tickets[ $id-1 ]['ticket_to'] : null;

        // Show modal
        $this->showTicket = true;
    }

    public function del_ticket( int $id ) {
        if ( $this->disabled === true ) return;

        $tickets = $this->mission->tickets;
        if( $id < 1 || $id > count($tickets)) return;
        if(isset($tickets[$id-1])) unset( $tickets[$id-1] );
        $this->mission->tickets = array_values( $tickets );
        $this->modified = true;
    }

    // Valide le formulaire et stocke le résultat en json dans la mission
    public function save_ticket() {
        // Validate current edited ticket
        $current_ticket = $this->validate( $this->ticket_rules() );

        // Get current tickets list
        $tickets = $this->mission->tickets;

        // New ticket or editing existing ticket, add to array
        if( !empty( $this->ticket_id ) ) {
            $tickets[ $this->ticket_id - 1 ] = $current_ticket;
        } else {
            $tickets[] = $current_ticket;
        }

        // Convert array to json
        $this->mission->tickets = $tickets;

        $this->modified = true;

        $this->close_ticket();
    }

    // End Tickets

    // Start Extra

    public function close_extra() {
        // Hide modal
        $this->showExtra = false;

        // Reset form
        $this->extra_meal         = null;
        $this->extra_taxi         = null;
        $this->extra_transport    = null;
        $this->extra_personal_car = null;
        $this->extra_rental_car   = null;
        $this->extra_parking      = null;
        $this->extra_registration = null;
        $this->extra_others       = '';
    }

    // Affiche le modal d'édition des frais prévisionnels après en avoir initialisé les valeurs
    public function edit_extra() {
        if ( $this->disabled === true ) return;

        // Get from json to array
        $extra = $this->mission->extra;

        // Initialize values
        $this->extra_meal         = isset( $extra['extra_meal'] ) ? $extra['extra_meal'] : false;
        $this->extra_taxi         = isset( $extra['extra_taxi'] ) ? $extra['extra_taxi'] : false;
        $this->extra_transport    = isset( $extra['extra_transport'] ) ? $extra['extra_transport'] : false;
        $this->extra_personal_car = isset( $extra['extra_personal_car'] ) ? $extra['extra_personal_car'] : false;
        $this->extra_rental_car   = isset( $extra['extra_rental_car'] ) ? $extra['extra_rental_car'] : false;
        $this->extra_parking      = isset( $extra['extra_parking'] ) ? $extra['extra_parking'] : false;
        if( empty( $this->mission->conf_amount ) )
            $this->extra_registration = isset( $extra['extra_registration'] ) ? $extra['extra_registration'] : false;
        $this->extra_others       = isset( $extra['extra_others'] ) ? $extra['extra_others'] : '';

        // Show modal
        $this->showExtra = true;
    }

    // Valide le formulaire et stocke le résultat en json dans la mission
    public function save_extra() {
        if( empty( $this->mission->conf_amount ) )
            $this->extra_registration = false;

        $this->mission->extra = $this->validate( $this->extra_rules() );

        $this->modified = true;

        $this->close_extra();
    }

    // End Extra

    public function makeBlankMission()
    {
        return Mission::make([
            'user_id'        => Auth()->id(),
            'hotels'         => [],
            'status'         => 'draft',
            'dest_country'   => 'FR',
            'conference'     => false,
            'conf_currency'  => 'EUR',
            'costs'          => false,
            'from'           => true,
            'to'             => true,
            'tickets'        => [],
            'accomodation'   => false,
            'extra'        => [],
            ]);
    }

    public function save()
    {
        $creation = is_null( $this->mission->id );

        //Force json encodage
        $this->mission->hotels = $this->mission->hotels;
        $this->mission->tickets = $this->mission->tickets;
        $this->mission->extra = $this->mission->extra;

        $this->withValidator(function (Validator $validator) {
                if ($validator->fails()) {
                    $this->emitSelf('notify-error');
                }
        })->validate();

        $this->mission->save();

        if ( $creation ) {
            // Mise à jour de l'url à la création
            $this->emit('urlChange', route('edit-mission',$this->mission->id));
        }

        // Traitement des uploads

        // Create user documents directory if not exists
        if( !empty( $this->programme ) || !empty( $this->uploads ) ) {
            $path = 'docs/'.$this->mission->user_id.'/';
            Storage::makeDirectory( $path );
        }

        // Sauvegarde du programme si présent
        if( !empty( $this->programme ) ) {
            // Store file in directory
            $filename = $this->programme->storeAs( '/'.$path, $this->programme->hashName() );

            // Create file in BDD
            Document::create([
                "name" => Document::filter_filename( $this->programme->getClientOriginalName() ),
                "type" => 'programme',
                "size" => Storage::size( $filename ),
                "filename" => $this->programme->hashName(),
                "user_id" => $this->mission->user_id,
                "documentable_id" => $this->mission->id,
                "documentable_type" => Mission::class,
            ]);
        }

        // Sauvegarde des fichiers ajoutés
        if( !empty( $this->uploads ) ) {

            foreach( $this->uploads as $file ) {
                // Store file in directory
                $filename = $file->storeAs( '/'.$path, $file->hashName() );

                // Create file in BDD
                Document::create([
                    "name" => Document::filter_filename( $file->getClientOriginalName() ),
                    "type" => 'document',
                    "size" => Storage::size( $filename ),
                    "filename" => $file->hashName(),
                    "user_id" => $this->mission->user_id,
                    "documentable_id" => $this->mission->id,
                    "documentable_type" => Mission::class,
                ]);
            }
        }

        // Reset des composants filepond
        if( !empty( $this->programme ) || !empty( $this->uploads ) ) {
            $this->dispatchBrowserEvent('pondReset');
        }

        // Suppression des fichiers à supprimer
        foreach( $this->del_docs as $id ) {

            Document::findOrFail( $id )->delete() ;

        }

        $this->reset(['uploads','modified','del_docs','programme']);
        $this->emit('refreshMission');
        $this->emitSelf('notify-saved');
        $this->statesUpdate();

        if ( $this->mission->status === 'draft' && auth()->user()->cannot('manage-users') ) {
            $this->showInformationMessage = 'submit-mission';
        }

        if ( array_key_exists( 'status', $this->mission->getChanges()) && $this->mission->status !== 'draft') {
            // Envoi de mail lors d'un changement de status uniquement
            $user = User::findOrFail($this->mission->user_id);
            Mail::to( $user )->send( new MissionStatusChange( $this->mission, $user->name, auth()->user()->name) );
        }
    }
}
