<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class NewMessage extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The object instance.
     *
     * @var object
     */
    public $object;

    /**
     * The destination user name.
     *
     * @var string
     */
    public $name;

    /**
     * The message author's name.
     *
     * @var string
     */
    public $author;

    /**
     * The object's url.
     *
     * @var string
     */
    public $url;

    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($object, $name = '', $author = null)
    {
        $this->object = $object;
        $this->name = $name;
        $this->author = $author;
        switch (get_class($object)) {
        case \App\Models\Order::class: $this->url = route('edit-order', [$object, '#messaging']);
            break;
        case \App\Models\Mission::class: $this->url = route('edit-mission', [$object, '#messaging']);
            break;
        case \App\Models\Expense::class: $this->url = route('edit-expense', [$object->mission, $object, '#messaging']);
            break;
        case \App\Models\Purchase::class: $this->url = route('edit-purchase', [$object, '#messaging']);
            break;
        }
    }

    /**
     * Build the message.
     */
    public function build(): static
    {
        return $this->subject('['.config('app.name').'] '.__('New message'))
            ->view('emails.new-message');
    }
}
