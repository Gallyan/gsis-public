<?php

namespace App\Http\Livewire;

use App\Models\User;
use App\Models\Order;
use App\Models\Manager;
use App\Mail\OrderStatusChange;
use Livewire\Component;
use App\Models\Document;
use Livewire\WithFileUploads;
use Illuminate\Validation\Validator;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Storage;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;

class EditOrder extends Component
{
    use WithFileUploads, AuthorizesRequests;

    public Order $order;
    public $uploads = [];
    public $del_docs = [];
    public $modified = false; // True if form is modified and need to be saved
    public $disabled = false; // True if user can't modify current editing Order
    public $disabledStatuses = []; // List of disabled status
    public $isAuthManager = false;

    // For Modal Book editing
    public $showModal = false;
    public $showInformationMessage = false;
    public string $title = '';
    public string $author = '';
    public string $isbn = '';
    public string $edition = 'paper';
    public int $book_id;

    protected function rules() { return [
        'order.user_id'        => 'required|exists:users,id',
        'order.subject'        => 'required|string|max:255',
        'order.institution_id' => 'required|exists:institutions,id',
        'order.supplier'       => 'nullable|string|max:255',
        'uploads'              => 'nullable|array',
        'uploads.*'            => 'mimes:xls,xlsx,doc,docx,pdf,zip,jpg,png,gif,bmp,webp,svg|max:10240',
        'order.books'          => 'sometimes|array',
        'order.comments'       => 'nullable|string',
        'order.status'         => 'required|in:'.collect(Order::STATUSES)->keys()->implode(','),
        'order.amount'          => 'nullable|float',
    ]; }

    protected function book_rules() { return [
        'title'  => 'required|string',
        'author' => 'required|string',
        'isbn'   => 'required|string',
        'edition' => 'required|in:'.collect(Order::EDITION)->keys()->implode(','),
    ]; }

    protected function messages() { return [
        'uploads.*.image' => __('The :filename file must be an image.'),
        'uploads.*.max' => __('The size of the :filename file cannot exceed :max kilobytes.'),
        'uploads.*.mimes' => __('The file :filename must be a file of type: :values.'),
        'uploads.*.mimetypes' => __('The file :filename must be a file of type: :values.'),
    ];}

    protected $listeners = ['refreshOrder' => '$refresh'];

    public function mount( $id = null ) {
        $this->isAuthManager = auth()->user()->can('manage-users');

        if ( is_null($id) ) {
            $this->order = $this->makeBlankOrder();
        } else {
            $this->order = Order::findOrFail($id);

            if ( ! auth()->user()->can('manage-users') && auth()->id() !== $this->order->user_id )
                abort(403);
        }

        $this->reset(['uploads','modified','del_docs']);
        $this->dispatchBrowserEvent('pondReset');
        $this->resetValidation();
        $this->statesUpdate();
    }

    public function init() {
        $this->mount( $this->order->id );
    }

    public function statesUpdate() {
        if ( $this->order->status === 'cancelled' ) {
            // Commande annulée, personne ne peut plus rien faire
            $this->disabled = true;
            $this->disabledStatuses = array_keys(Order::STATUSES);

        } elseif ( auth()->user()->can('manage-users') ) {
            // Gestionnnaire
            if ( $this->order->user_id === auth()->id() ) {
                // Le gestionnaire est aussi l'auteur de la commande

                if ( $this->order->status === 'draft' ) {
                    $this->disabled = false;
                    $this->disabledStatuses = array_diff( array_keys( Order::STATUSES ), [ 'draft', 'on-hold', 'cancelled' ] );
                } elseif ( $this->order->status === 'on-hold' ) {

                    $this->disabled = false;
                    if ( $this->order->managers->doesntContain( 'user_id', auth()->id() ) ) {
                        $this->disabledStatuses = array_diff( array_keys( Order::STATUSES ), [ 'draft', 'on-hold', 'cancelled' ] );
                    } else {
                        $this->disabledStatuses = [];
                    }

                } elseif ( $this->order->status === 'in-progress' ) {
                    if ( $this->order->managers->doesntContain( 'user_id', auth()->id() ) ) {
                        $this->disabled = true;
                        $this->disabledStatuses = array_keys(Order::STATUSES);

                    } else {
                        $this->disabled = false;
                        $this->disabledStatuses = [ 'draft', 'on-hold' ];
                    }

                } elseif ( $this->order->status === 'processed' ) {
                    if ( $this->order->managers->doesntContain( 'user_id', auth()->id() ) ) {
                        $this->disabled = true;
                        $this->disabledStatuses = array_keys( Order::STATUSES );

                    } else {
                        $this->disabled = false;
                        $this->disabledStatuses = array_keys(Order::STATUSES);
                    }
                }

            } else {
                // Le gestionnaire n'est pas l'auteur de la commande

                if ( $this->order->managers->doesntContain( 'user_id', auth()->id() ) ) {
                    // Le gestionnaire n'est pas associé à la commande, il ne peut rien faire sans s'associer
                    $this->disabled = true;
                    $this->disabledStatuses = array_keys(Order::STATUSES);

                } elseif ( $this->order->status === 'draft' ) {
                    $this->disabled = true;
                    $this->disabledStatuses = array_keys(Order::STATUSES);

                } elseif ( $this->order->status === 'on-hold' ) {
                    $this->disabled = false;
                    $this->disabledStatuses = [ 'draft' ];

                } elseif ( $this->order->status === 'in-progress' ) {
                    $this->disabled = false;
                    $this->disabledStatuses = [ 'draft', 'on-hold' ];

                } elseif ( $this->order->status === 'processed' ) {
                    // Une commande terminée ne peut plus changer de status
                    $this->disabled = false;
                    $this->disabledStatuses = array_keys(Order::STATUSES);
                }
            }
        } else {
            // Utilisateur
            if ( in_array( $this->order->status, [ 'draft', 'on-hold' ] ) ) {
                $this->disabled = false;
                $this->disabledStatuses = array_diff( array_keys( Order::STATUSES ), [ 'draft', 'on-hold', 'cancelled' ] );
            } else {
                // Une fois la commande soumise, l'utilisateur ne peut plus rien faire
                $this->disabled = true;
                $this->disabledStatuses = array_keys( Order::STATUSES );
            }
        }
    }

    public function render()
    {
        return view('livewire.edit-order')
            ->layoutData(['pageTitle' => __('Purchase Order').' '.$this->order->id ]);
    }

    public function close_modal() {
        $this->showModal = false;
        $this->title = $this->author = $this->isbn = '';  $this->edition = 'paper'; // Reset form
        unset($this->book_id);
    }

    public function edit_book( int $id ) {
        if ( $this->disabled === true ) return;

        $books = $this->order->books;

        if( $id < 1 || $id > count($books)) return;

        $this->book_id = $id;
        $this->title   = isset( $books[ $id-1 ]['title'] ) ? $books[ $id-1 ]['title'] : '';
        $this->author  = isset( $books[ $id-1 ]['author'] ) ? $books[ $id-1 ]['author'] : '';
        $this->isbn    = isset( $books[ $id-1 ]['isbn'] ) ? $books[ $id-1 ]['isbn'] : '';
        $this->edition = isset( $books[ $id-1 ]['edition'] ) ? $books[ $id-1 ]['edition'] : 'paper';

        $this->showModal = true;
    }

    public function del_book( int $id ) {
        if ( $this->disabled === true ) return;

        $books = $this->order->books;
        if( $id < 1 || $id > count($books)) return;
        if(isset($books[$id-1])) unset( $books[$id-1] );
        $this->order->books = array_values( $books );
        $this->modified = true;
    }

    // Ajoute un livre à la liste json des livres à commander
    public function add_book() {
        $current_book = $this->validate( $this->book_rules() );

        $books = $this->order->books;
        if( !empty( $this->book_id ) ) {
            $books[ $this->book_id - 1 ] = $current_book;
        } else {
            $books[] = $current_book;
        }

        $this->order->books = $books;

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

    // Associate current manager to Order
    public function associate() {
        Manager::create([
            'user_id' => auth()->id(),
            'manageable_id' => $this->order->id,
            'manageable_type' => Order::class,
        ]);
        if ( $this->order->status === 'on-hold' ) {
            $this->order->update(['status'=>'in-progress']);
            $user = User::findOrFail($this->order->user_id);
            Mail::to( $user )->send( new OrderStatusChange( $this->order, $user->name, auth()->user()->name) );
        }
        $this->emit('refreshOrder');
        $this->emit('refreshMessages');
        $this->init();
    }

    // Dissociate current manager from Order if he's not the only one
    public function dissociate() {
        // Check if manager is not the only one
        if ( count($this->order->managers) > 1 ) {
            Manager::where('user_id','=',auth()->id())
                ->where('manageable_type','=',Order::class)
                ->where('manageable_id','=',$this->order->id)
                ->delete();
                $this->emit('refreshOrder');
                $this->init();
            }
    }

    public function updated($propertyName) {
        if( in_array( $propertyName, array_keys($this->book_rules()) ) ) {
            $this->validateOnly($propertyName, $this->book_rules());
        } else {
            $this->validateOnly($propertyName);
            if ( $propertyName !== "showModal" ) $this->modified = !empty($this->order->getDirty()) ;
        }
    }

    public function updatedUploads() {
        if ( $this->uploads ) {

            $this->validateOnly( 'uploads.*' );

            $this->modified = true;
        }
    }

    public function makeBlankOrder()
    {
        return Order::make([
            'user_id' => Auth()->id(),
            'books'   => [],
            'status' => 'draft',
        ]);
    }

    public function save()
    {
        $creation = is_null( $this->order->id );

        $this->order->books = $this->order->books; //Force json encodage

        $this->withValidator(function (Validator $validator) {
                if ($validator->fails()) {
                    $this->emitSelf('notify-error');
                }
        })->validate();

        $this->order->save();

        if ( $creation ) {
            // Mise à jour de l'url à la création
            $this->emit('urlChange', route('edit-order',$this->order->id));
        }

        // Sauvegarde des fichiers ajoutés
        if( !empty( $this->uploads ) ) {

            // Create user documents directory if not exists
            $path = 'docs/'.$this->order->user_id.'/';
            Storage::makeDirectory( $path );

            foreach( $this->uploads as $file ) {
                // Store file in directory
                $filename = $file->storeAs( '/'.$path, $file->hashName() );

                // Create file in BDD
                Document::create([
                    "name" => Document::filter_filename( $file->getClientOriginalName() ),
                    "type" => 'quotation',
                    "size" => Storage::size( $filename ),
                    "filename" => $file->hashName(),
                    "user_id" => $this->order->user_id,
                    "documentable_id" => $this->order->id,
                    "documentable_type" => Order::class,
                ]);
            }
            $this->dispatchBrowserEvent('pondReset');
        }

        // Suppression des fichiers à supprimer
        foreach( $this->del_docs as $id ) {

            Document::findOrFail( $id )->delete() ;

        }

        $this->reset(['uploads','modified','del_docs']);
        $this->emit('refreshOrder');
        $this->emitSelf('notify-saved');
        $this->statesUpdate();

        if ( $this->order->status === 'draft' && auth()->user()->cannot('manage-users') ) {
            $this->showInformationMessage = 'submit-order';
        }

        if ( array_key_exists( 'status', $this->order->getChanges()) && $this->order->status !== 'draft') {
            // Envoi de mail lors d'un changement de status uniquement
            $user = User::findOrFail($this->order->user_id);
            Mail::to( $user )->send( new OrderStatusChange( $this->order, $user->name, auth()->user()->name) );
        }
    }
}
