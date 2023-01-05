<?php

namespace App\Http\Livewire;

use App\Models\User;
use App\Models\Mission;
use App\Models\Expense;
use App\Mail\ExpenseStatusChange;
use Livewire\Component;
use App\Models\Document;
use Livewire\WithFileUploads;
use Illuminate\Validation\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class EditExpense extends Component
{
    use WithFileUploads, AuthorizesRequests;

    public Expense $expense;
    public Mission $mission;
    public $uploads = [];
    public $del_docs = [];
    public $modified = false; // True if form is modified and need to be saved
    public $disabled = false; // True if user can't modify current editing Mission
    public $disabledStatuses = []; // List of disabled status
    public $showTransport = false;
    public $showInformationMessage = false;
    public $isAuthManager = false;

    // Transport
    public $transport_id;
    public $transport_mode;
    public $transport_date;
    public $transport_dest;
    public $transport_type;
    public $transport_number;
    public $transport_amount;
    public $transport_dist;
    public $transport_route;
    public $transport_currency;

    protected function rules() { return [
        'expense.id'                                => 'nullable|exists:missions,id',
        'expense.mission_id'                        => 'required|exists:missions,id',
        'expense.user_id'                           => 'required|exists:users,id',
        'expense.status'                            => 'required|in:'.collect(Expense::STATUSES)->keys()->implode(','),
        'expense.actual_costs_meals'                => 'nullable|array',
        'expense.actual_costs_meals.*.acm_date'     => 'required|date',
        'expense.actual_costs_meals.*.acm_type'     => 'required|in:lunch,dinner',
        'expense.actual_costs_meals.*.acm_amount'   => 'required|float',
        'expense.actual_costs_meals.*.acm_currency' => 'required|string|max:3',
        'expense.flat_rate_lunch'                   => 'nullable|integer|min:0',
        'expense.flat_rate_dinner'                  => 'nullable|integer|min:0',
        'expense.transports'                        => 'nullable|array',
        'expense.hotels'                            => 'nullable|array',
        'expense.hotels.*.hotel_date'               => 'required|date',
        'expense.hotels.*.hotel_name'               => 'required|string',
        'expense.hotels.*.hotel_nights'             => 'required|integer|min:1',
        'expense.hotels.*.hotel_amount'             => 'required|float',
        'expense.hotels.*.hotel_currency'           => 'required|string|max:3',
        'expense.registrations'                     => 'nullable|array',
        'expense.registrations.*.reg_name'          => 'required|string',
        'expense.registrations.*.reg_amount'        => 'required|float',
        'expense.registrations.*.reg_currency'      => 'required|string|max:3',
        'expense.miscs'                             => 'nullable|array',
        'expense.miscs.*.misc_object'               => 'required|string',
        'expense.miscs.*.misc_date'                 => 'required|date',
        'expense.miscs.*.misc_amount'               => 'required|float',
        'expense.miscs.*.misc_currency'             => 'required|string|max:3',
        'expense.comments'                          => 'nullable|string',
    ]; }

    protected function transport_rules() { return [
        'transport_mode'     => 'required|in:'.collect(Expense::TRANSPORTS)->keys()->implode(','),
        'transport_date'     => 'required|date_format:Y-m-d',
        'transport_dest'     => 'sometimes|required_if:transport_mode,train,flight|nullable|string',
        'transport_type'     => 'sometimes|required_if:transport_mode,public|nullable|string',
        'transport_number'   => 'sometimes|required_if:transport_mode,public|nullable|integer|min:0',
        'transport_dist'     => 'sometimes|required_if:transport_mode,personal|nullable|integer|min:1',
        'transport_route'    => 'sometimes|required_if:transport_mode,taxi,personal|nullable|string',
        'transport_amount'   => 'sometimes|required_if:transport_mode,train,flight,public,taxi|nullable|float',
        'transport_currency' => 'sometimes|required_if:transport_mode,train,flight,public,taxi|required_with:transport_amount|nullable|string|size:3',
    ]; }

    protected function validationAttributes() { return [
        'expense.hotels.*.hotel_date'               => __('date'),
        'expense.hotels.*.hotel_name'               => __('name'),
        'expense.hotels.*.hotel_nights'             => __('no. of nights'),
        'expense.hotels.*.hotel_amount'             => __('amount'),
        'expense.hotels.*.hotel_currency'           => __('currency'),
        'expense.actual_costs_meals.*.acm_date'     => __('date'),
        'expense.actual_costs_meals.*.acm_type'     => __('type'),
        'expense.actual_costs_meals.*.acm_amount'   => __('amount'),
        'expense.actual_costs_meals.*.acm_currency' => __('currency'),
        'expense.registrations.*.reg_name'          => __('conference'),
        'expense.registrations.*.reg_amount'        => __('amount'),
        'expense.registrations.*.reg_currency'      => __('currency'),
        'expense.miscs.*.misc_object'               => __('object'),
        'expense.miscs.*.misc_date'                 => __('date'),
        'expense.miscs.*.misc_amount'               => __('amount'),
        'expense.miscs.*.misc_currency'             => __('currency'),
        'transport_mode'                            => __('travel mode'),
        'transport_date'                            => __('date'),
        'transport_dest'                            => __('destination'),
        'transport_type'                            => __('transport type'),
        'transport_number'                          => __('no. of tickets'),
        'transport_dist'                            => __('distance'),
        'transport_route'                           => __('route'),
        'transport_amount'                          => __('amount'),
        'transport_currency'                        => __('currency'),
    ]; }

    protected function messages() { return [
        'uploads.*.image'                => __('The :filename file must be an image.'),
        'uploads.*.max'                  => __('The size of the :filename file cannot exceed :max kilobytes.'),
        'uploads.*.mimes'                => __('The file :filename must be a file of type: :values.'),
        'uploads.*.mimetypes'            => __('The file :filename must be a file of type: :values.'),
        'transport_date.required'        => __('The :attribute field is required.'),
        'transport_dest.required_if'     => __('The :attribute field is required.'),
        'transport_type.required_if'     => __('The :attribute field is required.'),
        'transport_number.required_if'   => __('The :attribute field is required.'),
        'transport_dist.required_if'     => __('The :attribute field is required.'),
        'transport_route.required_if'    => __('The :attribute field is required.'),
        'transport_amount.required_if'   => __('The :attribute field is required.'),
        'transport_currency.required_if' => __('The :attribute field is required.'),
    ];}

    protected $listeners = ['refreshExpense' => '$refresh'];

    public function mount( Mission $mission ) {
        $this->isAuthManager = auth()->user()->can('manage-users');

        $this->mission = $mission;

        $this->expense = Expense::where( 'mission_id', '=', $this->mission->id )->first() ?? $this->makeBlankExpense();

        if ( ! auth()->user()->can('manage-users') && auth()->id() !== $this->mission->user_id )
            abort(403);

        $this->reset(['uploads','modified','del_docs']);
        $this->dispatchBrowserEvent('pondReset');
        $this->resetValidation();
        $this->statesUpdate();
    }

    public function init() {
        $this->mount( Mission::find( $this->mission->id ) );
    }

    public function statesUpdate() {
        if ( $this->expense->status === 'cancelled' ) {
            // Commande annulée, personne ne peut plus rien faire
            $this->disabled = true;
            $this->disabledStatuses = array_keys(Expense::STATUSES);

        } elseif ( auth()->user()->can('manage-users') ) {
            // Gestionnnaire
            if ( $this->expense->user_id === auth()->id() ) {
                // Le gestionnaire est aussi l'auteur de la commande

                if ( $this->expense->status === 'draft' ) {
                    $this->disabled = false;
                    $this->disabledStatuses = array_diff( array_keys( Expense::STATUSES ), [ 'draft', 'on-hold', 'cancelled' ] );
                } elseif ( $this->expense->status === 'on-hold' ) {

                    $this->disabled = false;
                    if ( $this->mission->managers->doesntContain( 'user_id', auth()->id() ) ) {
                        $this->disabledStatuses = array_diff( array_keys( Expense::STATUSES ), [ 'draft', 'on-hold', 'cancelled' ] );
                    } else {
                        $this->disabledStatuses = [];
                    }

                } elseif ( $this->expense->status === 'in-progress' ) {
                    if ( $this->mission->managers->doesntContain( 'user_id', auth()->id() ) ) {
                        $this->disabled = true;
                        $this->disabledStatuses = array_keys( Expense::STATUSES );

                    } else {
                        $this->disabled = false;
                        $this->disabledStatuses = [ 'draft', 'on-hold' ];
                    }

                } elseif ( $this->expense->status === 'processed' ) {
                    if ( $this->mission->managers->doesntContain( 'user_id', auth()->id() ) ) {
                        $this->disabled = true;
                        $this->disabledStatuses = array_keys( Expense::STATUSES );

                    } else {
                        $this->disabled = false;
                        $this->disabledStatuses = array_keys( Expense::STATUSES );
                    }
                }

            } else {
                // Le gestionnaire n'est pas l'auteur de la commande

                if ( $this->mission->managers->doesntContain( 'user_id', auth()->id() ) ) {
                    // Le gestionnaire n'est pas associé à la commande, il ne peut rien faire sans s'associer
                    $this->disabled = true;
                    $this->disabledStatuses = array_keys(Expense::STATUSES);

                } elseif ( $this->expense->status === 'draft' ) {
                    $this->disabled = true;
                    $this->disabledStatuses = array_keys(Expense::STATUSES);

                } elseif ( $this->expense->status === 'on-hold' ) {
                    $this->disabled = false;
                    $this->disabledStatuses = [ 'draft' ];

                } elseif ( $this->expense->status === 'in-progress' ) {
                    $this->disabled = false;
                    $this->disabledStatuses = [ 'draft', 'on-hold' ];

                } elseif ( $this->expense->status === 'processed' ) {
                    // Une commande terminée ne peut plus changer de status
                    $this->disabled = false;
                    $this->disabledStatuses = array_keys(Expense::STATUSES);
                }
            }
        } else {
            // Utilisateur
            if ( in_array( $this->expense->status, [ 'draft', 'on-hold' ] ) ) {
                $this->disabled = false;
                $this->disabledStatuses = array_diff( array_keys( Expense::STATUSES ), [ 'draft', 'on-hold', 'cancelled' ] );
            } else {
                // Une fois la demande soumise, l'utilisateur ne peut plus rien faire
                $this->disabled = true;
                $this->disabledStatuses = array_keys( Expense::STATUSES );
            }
        }
    }

    public function render()
    {
        return view('livewire.edit-expense')
            ->layoutData(['pageTitle' => __('Related expenses of mission').' '.$this->mission->id ]);
    }

    public function makeBlankExpense()
    {
        return Expense::make([
            'mission_id'         => $this->mission->id,
            'user_id'            => Auth()->id(),
            'status'             => 'draft',
            'actual_costs_meals' => [],
            'transports'         => [],
            'hotels'             => [],
            'registrations'      => [],
            'miscs'              => [],
            ]);
    }

    public function updated($propertyName) {
        if( str_starts_with($propertyName, 'expense.actual_costs_meals') ) {
            $line = preg_replace('/expense\.actual_costs_meals\.([0-9]+)\.acm_.+/', '$1', $propertyName);
            $values = array_filter( $this->expense->actual_costs_meals[$line] );
            if ( count($values) == 4 ) {
                $this->validateOnly('expense.actual_costs_meals.'.$line.'.acm_date');
                $this->validateOnly('expense.actual_costs_meals.'.$line.'.acm_type');
                $this->validateOnly('expense.actual_costs_meals.'.$line.'.acm_amount');
                $this->validateOnly('expense.actual_costs_meals.'.$line.'.acm_currency');
            }

        }elseif( str_starts_with($propertyName, 'expense.hotels') ) {
                $line = preg_replace('/expense\.hotels\.([0-9]+)\.hotel_.+/', '$1', $propertyName);
                $values = array_filter( $this->expense->hotels[$line] );
                if ( count($values) == 5 ) {
                    $this->validateOnly('expense.hotels.'.$line.'.hotel_date');
                    $this->validateOnly('expense.hotels.'.$line.'.hotel_name');
                    $this->validateOnly('expense.hotels.'.$line.'.hotel_nights');
                    $this->validateOnly('expense.hotels.'.$line.'.hotel_amount');
                    $this->validateOnly('expense.hotels.'.$line.'.hotel_currency');
                }

        }elseif( str_starts_with($propertyName, 'expense.registrations') ) {
            $line = preg_replace('/expense\.registrations\.([0-9]+)\.reg_.+/', '$1', $propertyName);
            $values = array_filter( $this->expense->registrations[$line] );
            if ( count($values) == 3 ) {
                $this->validateOnly('expense.registrations.'.$line.'.reg_name');
                $this->validateOnly('expense.registrations.'.$line.'.reg_amount');
                $this->validateOnly('expense.registrations.'.$line.'.reg_currency');
            }

        }elseif( str_starts_with($propertyName, 'expense.miscs') ) {
            $line = preg_replace('/expense\.miscs\.([0-9]+)\.misc_.+/', '$1', $propertyName);
            $values = array_filter( $this->expense->miscs[$line] );
            if ( count($values) == 5 ) {
                $this->validateOnly('expense.miscs.'.$line.'.misc_object');
                $this->validateOnly('expense.miscs.'.$line.'.misc_date');
                $this->validateOnly('expense.miscs.'.$line.'.misc_amount');
                $this->validateOnly('expense.miscs.'.$line.'.misc_currency');
            }

        } elseif( in_array( $propertyName, array_keys($this->transport_rules()) ) ) {
            $this->validateOnly($propertyName, $this->transport_rules());

        } else {
            $this->validateOnly($propertyName);
        }
        $this->modified = !empty($this->expense->getDirty()) ;
    }

    public function save() {
        $creation = is_null( $this->expense->id );

        //Force json encodage
        $this->expense->actual_costs_meals = $this->expense->actual_costs_meals;
        $this->expense->transports = $this->expense->transports;
        $this->expense->hotels = $this->expense->hotels;
        $this->expense->registrations = $this->expense->registrations;
        $this->expense->miscs = $this->expense->miscs;

        $this->withValidator(function (Validator $validator) {
                if ($validator->fails()) {
                    $this->emitSelf('notify-error');
                }
        })->validate();

        if ( $creation ) $this->expense->id = $this->mission->id;

        // Sauvegarde des dépenses
        $this->expense->save();

        if ( $creation ) {
            // Mise à jour de l'url à la création
            $this->emit('urlChange', route('edit-expense', [$this->mission,$this->expense]));
        }

        // Traitement des uploads
/*
        // Create user documents directory if not exists
        if( !empty( $this->uploads ) ) {
            $path = 'docs/'.$this->mission->user_id.'/';
            Storage::makeDirectory( $path );
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
                    "documentable_type" => Expense::class,
                ]);
            }
        }

        // Reset des composants filepond
        if( !empty( $this->uploads ) ) {
            $this->dispatchBrowserEvent('pondReset');
        }

        // Suppression des fichiers à supprimer
        foreach( $this->del_docs as $id ) {

            Document::findOrFail( $id )->delete() ;

        }*/

        $this->reset(['uploads','modified','del_docs']);
        $this->emit('refreshExpense');
        $this->emitSelf('notify-saved');
        $this->statesUpdate();

        if ( $this->expense->status === 'draft' && auth()->user()->cannot('manage-users') ) {
            $this->showInformationMessage = 'submit-expense';
        }

        if ( array_key_exists( 'status', $this->expense->getChanges()) && $this->expense->status !== 'draft') {
            // Envoi de mail lors d'un changement de status uniquement
            $user = User::findOrFail($this->expense->user_id);
            Mail::to( $user )->send( new ExpenseStatusChange( $this->mission, $user->name, auth()->user()->name) );
        }
    }

    /* Start Gestion des repas */

    public function add_acm() {
        if ( $this->disabled === true ) return;

        $acm = $this->expense->actual_costs_meals;
        $acm[] = [
            'acm_date'=>date('Y-m-d'),
            'acm_type'=>'lunch',
            'acm_amount'=>null,
            'acm_currency'=>'EUR'
        ];
        $this->expense->actual_costs_meals = $acm;

        $this->modified = true;
    }

    public function del_acm( int $id ) {
        if ( $this->disabled === true ) return;

        $acm = $this->expense->actual_costs_meals;
        if( $id < 1 || $id > count($acm)) return;
        if(isset($acm[$id-1])) unset( $acm[$id-1] );
        $this->expense->actual_costs_meals = array_values( $acm );

        $this->modified = !empty($this->expense->getDirty()) ;
    }

    /* End Gestion des repas */

    /* Start Gestion des hotels */

    public function add_hotel() {
        if ( $this->disabled === true ) return;

        $hotels = $this->expense->hotels;
        $hotels[] = [
            'hotel_date'=>null,
            'hotel_name'=>null,
            'hotel_nights'=>null,
            'hotel_amount'=>null,
            'hotel_currency'=>'EUR'
        ];
        $this->expense->hotels = $hotels;

        $this->modified = true;
    }

    public function del_hotel( int $id ) {
        if ( $this->disabled === true ) return;

        $hotels = $this->expense->hotels;
        if( $id < 1 || $id > count($hotels)) return;
        if(isset($hotels[$id-1])) unset( $hotels[$id-1] );
        $this->expense->hotels = array_values( $hotels );

        $this->modified = !empty($this->expense->getDirty()) ;
    }

    /* End Gestion des hotels */

    /* Start Gestion des registrations */

    public function add_reg() {
        if ( $this->disabled === true ) return;

        $registrations = $this->expense->registrations;
        $registrations[] = [
            'reg_name'=>null,
            'reg_amount'=>null,
            'reg_currency'=>'EUR'
        ];
        $this->expense->registrations = $registrations;

        $this->modified = true;
    }

    public function del_reg( int $id ) {
        if ( $this->disabled === true ) return;

        $registrations = $this->expense->registrations;
        if( $id < 1 || $id > count($registrations)) return;
        if(isset($registrations[$id-1])) unset( $registrations[$id-1] );
        $this->expense->registrations = array_values( $registrations );

        $this->modified = !empty($this->expense->getDirty()) ;
    }

    /* End Gestion des registrations */

    /* Start Gestion des miscs */

    public function add_misc() {
        if ( $this->disabled === true ) return;

        $miscs = $this->expense->miscs;
        $miscs[] = [
            'misc_object'=>null,
            'misc_date'=>null,
            'misc_amount'=>null,
            'misc_currency'=>'EUR'
        ];
        $this->expense->miscs = $miscs;

        $this->modified = true;
    }

    public function del_misc( int $id ) {
        if ( $this->disabled === true ) return;

        $miscs = $this->expense->miscs;
        if( $id < 1 || $id > count($miscs)) return;
        if(isset($miscs[$id-1])) unset( $miscs[$id-1] );
        $this->expense->miscs = array_values( $miscs );

        $this->modified = !empty($this->expense->getDirty()) ;
    }

    /* End Gestion des miscs */

    // Start Transports

    public function close_transport() {
        // Hide modal
        $this->showTransport = false;

        // Reset form
        $this->transport_mode      = null;
        $this->transport_date      = null;
        $this->transport_dest      = null;
        $this->transport_type      = null;
        $this->transport_number    = null;
        $this->transport_route     = null;
        $this->transport_amount    = null;
        $this->transport_currency  = 'EUR';

        unset($this->transport_id);
    }

    // Affiche le modal d'édition du transport après en avoir initialisé les valeurs
    public function edit_transport( int $id ) {
        if ( $this->disabled === true ) return;

        // Get from json to array
        $transports = $this->expense->transports;

        // Control
        if( $id < 1 || $id > count($transports)) return;

        // Initialize values
        $this->transport_id = $id;
        $this->transport_mode     = isset( $transports[ $id-1 ]['transport_mode'] ) ? $transports[ $id-1 ]['transport_mode'] : null;
        $this->transport_date     = isset( $transports[ $id-1 ]['transport_date'] ) ? $transports[ $id-1 ]['transport_date'] : null;
        $this->transport_dest     = isset( $transports[ $id-1 ]['transport_dest'] ) ? $transports[ $id-1 ]['transport_dest'] : null;
        $this->transport_type     = isset( $transports[ $id-1 ]['transport_type'] ) ? $transports[ $id-1 ]['transport_type'] : null;
        $this->transport_number   = isset( $transports[ $id-1 ]['transport_number'] ) ? $transports[ $id-1 ]['transport_number'] : null;
        $this->transport_route    = isset( $transports[ $id-1 ]['transport_route'] ) ? $transports[ $id-1 ]['transport_route'] : null;
        $this->transport_amount   = isset( $transports[ $id-1 ]['transport_amount'] ) ? $transports[ $id-1 ]['transport_amount'] : null;
        $this->transport_currency = isset( $transports[ $id-1 ]['transport_currency'] ) ? $transports[ $id-1 ]['transport_currency'] : 'EUR';

        // Show modal
        $this->showTransport = true;
    }

    public function del_transport( int $id ) {
        if ( $this->disabled === true ) return;

        $transports = $this->expense->transports;
        if( $id < 1 || $id > count($transports)) return;
        if(isset($transports[$id-1])) unset( $transports[$id-1] );
        $this->expense->transports = array_values( $transports );
        $this->modified = true;
    }

    public function updatedTransportMode() {

        $this->transport_date     = null;
        $this->transport_dest     = null;
        $this->transport_type     = null;
        $this->transport_number   = null;
        $this->transport_route    = null;
        $this->transport_amount   = null;
        $this->transport_currency = 'EUR';

        $this->resetValidation();
    }

    // Valide le formulaire et stocke le résultat en json dans la mission
    public function save_transport() {
        // Validate current edited transport
        $current_transport = $this->validate( $this->transport_rules() );

        // Get current transports list
        $transports = $this->expense->transports;

        // New transport or editing existing transport, add to array
        if( !empty( $this->transport_id ) ) {
            $transports[ $this->transport_id - 1 ] = $current_transport;
        } else {
            $transports[] = $current_transport;
        }

        // Convert array to json
        $this->expense->transports = $transports;

        $this->modified = true;

        $this->close_transport();
    }

    // End Transports
}
