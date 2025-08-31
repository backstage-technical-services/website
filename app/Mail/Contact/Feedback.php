<?php

namespace App\Mail\Contact;

use App\Mail\Mailable;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Collection;

class Feedback extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Variable to store the feedback.
     * @var array
     */
    private $feedback;

    /**
     * Create a new message instance.
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->feedback = $data;
    }

    /**
     * Build the message.
     * @return $this
     */
    public function build()
    {
        return $this->subject('Feedback received')
            ->markdown('emails.contact.feedback')
            ->with('feedback', $this->feedback);
    }
}
