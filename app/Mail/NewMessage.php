<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
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
    public function __construct( $object, $name = '', $author = null )
    {
        $this->object = $object;
        $this->name =  $name;
        $this->author = $author;
        switch( get_class($object) ) {
            case "App\Models\Order": $this->url = route('edit-order', [ $object, '#messaging' ]); break;
            case "App\Models\Mission": $this->url = route('edit-mission', [ $object, '#messaging' ]); break;
            case "App\Models\Expense": $this->url = route('edit-expense', [ $object->mission, $object, '#messaging' ]); break;
            case "App\Models\Purchase": $this->url = route('edit-purchase', [ $object, '#messaging' ]); break;
        }
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject( '['.config('app.name').'] '.__('You\'ve got new message'))
                    ->view('emails.new-message');
    }
}
