<?php

namespace App\Mail;

use App\Models\User;
use App\Models\Mission;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class MissionStatusChange extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The mission instance.
     *
     * @var \App\Models\Mission
     */
    public Mission $mission;

    /**
     * The destination user name.
     *
     * @var string
     */
    public $name;

    /**
     * The mission status.
     *
     * @var string
     */
    public $status;

    /**
     * The mission's manager's name.
     *
     * @var string
     */
    public $manager;

    /**
     * Create a new message instance.
     *
     * @param  \App\Models\Mission  $mission
     * @return void
     */
    public function __construct( Mission $mission, $name = '', $manager = null )
    {
        $this->mission = $mission;
        $this->status = $mission->status;
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
        return $this->subject( '['.config('app.name').'] '.__('Mission').' '.$this->mission->id.' '.__($this->status))
                    ->view('emails.mission-status-change');
    }
}
