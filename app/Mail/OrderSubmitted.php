<?php

namespace App\Mail;

use App\Models\User;
use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderSubmitted extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The order instance.
     *
     * @var \App\Models\Order
     */
    public $order;

    /**
     * The destination user name.
     *
     * @var string
     */
    public $name;

    /**
     * The order status.
     *
     * @var string
     */
    public $status;

    /**
     * The order's manager's name.
     *
     * @var string
     */
    public $manager;

    /**
     * Create a new message instance.
     *
     * @param  \App\Models\Order  $order
     * @return void
     */
    public function __construct( Order $order, $name = '', $manager = '' )
    {
        $this->order = $order;
        $this->status = $order->status;
        $this->name =  $name;
        $this->manager = $manager;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject( '['.config('app.name').'] '.__('Order').' '.$this->order->id.' '.__($this->status))
                    ->view('emails.orders.submitted');
    }
}
