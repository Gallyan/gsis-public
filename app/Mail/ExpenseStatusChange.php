<?php

namespace App\Mail;

use App\Models\Expense;
use App\Models\Mission;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ExpenseStatusChange extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * The mission instance.
     */
    public Mission $mission;

    /**
     * The expense instance.
     */
    public Expense $expense;

    /**
     * The destination user name.
     *
     * @var string
     */
    public $name;

    /**
     * The expense status.
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
     * @return void
     */
    public function __construct(Mission $mission, $name = '', $manager = null)
    {
        $this->mission = $mission;
        $this->expense = $mission->expense;
        $this->status = $this->expense->status;
        $this->name = $name;
        $this->manager = $manager;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->subject('['.config('app.name').'] '.__('Mission :id expenses', ['id' => $this->mission->id]).' '.__($this->status))
            ->view('emails.expense-status-change');
    }
}
