<?php

namespace App\Mail;

use App\Models\Order;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class OrderStatusChange extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The order instance.
     */
    public Order $order;

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
     * @return void
     */
    public function __construct(Order $order, $name = '', $manager = null)
    {
        $this->order = $order;
        $this->status = $order->status;
        $this->name = $name;
        $this->manager = $manager;
    }

    /**
     * Build the message.
     */
    public function build(): static
    {
        return $this->subject('['.config('app.name').'] '.__('Order').' '.$this->order->id.' '.__($this->status))
            ->view('emails.order-status-change');
    }
}
