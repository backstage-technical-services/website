<?php

namespace App\Mail\Users;

use App\Mail\Mailable;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;

class AccountCreated extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Variable to store the user's details.
     * @var array
     */
    private $user;

    /**
     * Create a new message instance.
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->user = $data;
    }

    /**
     * Build the message.
     * @return $this
     */
    public function build()
    {
        return $this->replyTo(config('bts.emails.account.created'))
            ->subject('Your new Backstage account')
            ->markdown('emails.users.created')
            ->with('user', $this->user);
    }
}
