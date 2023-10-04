<?php

namespace App\Mail;

use App\Models\Purchase;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class PurchaseStatusChange extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The purchase instance.
     */
    public Purchase $purchase;

    /**
     * The destination user name.
     *
     * @var string
     */
    public $name;

    /**
     * The purchase status.
     *
     * @var string
     */
    public $status;

    /**
     * The purchase's manager's name.
     *
     * @var string
     */
    public $manager;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct(Purchase $purchase, $name = '', $manager = null)
    {
        $this->purchase = $purchase;
        $this->status = $purchase->status;
        $this->name = $name;
        $this->manager = $manager;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build(): static
    {
        return $this->subject('['.config('app.name').'] '.__('Non-mission purchase').' '.$this->purchase->id.' '.__($this->status))
            ->view('emails.purchase-status-change');
    }
}
