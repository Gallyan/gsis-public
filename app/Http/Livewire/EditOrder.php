<?php

namespace App\Http\Livewire;

use App\Models\Order;
use Livewire\Component;

class EditOrder extends Component
{
    public Order $order;

    protected function rules() { return [
        'order.user_id' => 'required|exists:users,id',
        'order.subject' => 'required|string|max:255',
        'order.institution_id' => 'required|exists:institutions,id',
        'order.supplier' => 'sometimes|string|max:255',
        'order.books' => 'sometimes|json',
        'order.comments' => 'sometimes|string',
        'order.status' => 'required|in:'.collect(Order::STATUSES)->keys()->implode(','),
    ]; }

    public function mount( $id = null ) {
        if ( is_null($id) ) {
            $this->order = Order::make(['user_id'=>Auth()->user()->id]);
        } else {
            $this->order = Order::find($id);
        }
    }

    public function updated($propertyName) { $this->validateOnly($propertyName); }

    public function init() {
        if ( is_null($this->order->id) ) {
            $this->order = Order::make(['user_id'=>Auth()->user()->id]);
        } else {
            $this->order = Order::find($this->order->id);
        }
    }

    public function save()
    {
        $this->validate();

        $this->order->save();

        $this->emitSelf('notify-saved');
    }
}
