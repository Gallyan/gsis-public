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
    public $full_name;

    /**
     * The destination user locale.
     *
     * @var string
     */
    public $locale;

    /**
     * Create a new message instance.
     *
     * @param  \App\Models\Order  $order
     * @return void
     */
    public function __construct( Order $order, $full_name = '', $locale = null)
    {
        $this->order = $order;
        $this->full_name =  $full_name;
        $this->locale = $locale;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject( '[' . config('app.name') . '] ' . __('Order :id submitted', ['id' => $this->order->id]) )
                    ->view('emails.orders.submitted');
    }
}
