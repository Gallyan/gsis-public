<?php

namespace App\Http\Livewire;

use App\Models\Order;
use Livewire\Component;
use Illuminate\Validation\Validator;

class EditOrder extends Component
{
    public Order $order;

    public $showModal = false;

    public string $title = '';
    public string $author = '';
    public string $isbn = '';
    public int $book_id;

    protected function rules() { return [
        'order.user_id'        => 'required|exists:users,id',
        'order.subject'        => 'required|string|max:255',
        'order.institution_id' => 'required|exists:institutions,id',
        'order.supplier'       => 'nullable|string|max:255',
        'order.books'          => 'sometimes',
        'order.comments'       => 'nullable|string',
        'order.status'         => 'required|in:'.collect(Order::STATUSES)->keys()->implode(','),
    ]; }
    protected function book_rules() { return [
        'title'  => 'required|string',
        'author' => 'required|string',
        'isbn'   => 'required|string',
    ]; }

    public function mount( $id = null ) {
        if ( is_null($id) ) {
            $this->order = $this->makeBlankOrder();
        } else {
            $this->order = Order::find($id);
        }
    }

    public function render()
    {
        return view('livewire.edit-order')
            ->layoutData(['pageTitle' => __('Purchase Order').' '.$this->order->id ]);
    }

    public function close_modal() {
        $this->showModal = false;
        $this->title = $this->author = $this->isbn = ''; // Reset form
        unset($this->book_id);
    }

    public function edit_book( int $id ) {
        $books = $this->order->books;

        if( $id < 1 || $id > count($books)) return;

        $this->book_id = $id;
        $this->title  = $books[ $id-1 ]['title'];
        $this->author = $books[ $id-1 ]['author'];
        $this->isbn   = $books[ $id-1 ]['isbn'];

        $this->showModal = true;
    }

    public function del_book( int $id ) {
        $books = $this->order->books;
        if( $id < 1 || $id > count($books)) return;
        if(isset($books[$id-1])) unset( $books[$id-1] );
        $this->order->books = array_values( $books );
    }

    // Ajoute un livre à la liste json des livres à commander
    public function add_book() {
        $this->validate( $this->book_rules() );

        $books = $this->order->books;
        $current_book = [
            "title"  => $this->title,
            "author" => $this->author,
            "isbn"   => $this->isbn
        ];
        if( !empty( $this->book_id ) ) {
            $books[ $this->book_id - 1 ] = $current_book;
        } else {
            $books[] = $current_book;
        }

        $this->order->books = $books;

        $this->close_modal();
    }

    public function updated($propertyName) {
        if( in_array( $propertyName, array_keys($this->book_rules()) ) ) {
            $this->validateOnly($propertyName, $this->book_rules());
        } else {
            $this->validateOnly($propertyName);
        }
    }

    public function makeBlankOrder()
    {
        return Order::make([
            'user_id' => Auth()->user()->id,
            'books'   => [],
        ]);
    }

    public function init() {
        if ( is_null($this->order->id) ) {
            $this->order = $this->makeBlankOrder();
        } else {
            $this->order = Order::find( $this->order->id );
        }
    }

    public function save()
    {
        $this->order->books = $this->order->books; //Force json encodage

        $this->withValidator(function (Validator $validator) {
                if ($validator->fails()) {
                    $this->emitSelf('notify-error');
                }
        })->validate();

        $this->order->save();

        $this->emitSelf('notify-saved');
    }
}
