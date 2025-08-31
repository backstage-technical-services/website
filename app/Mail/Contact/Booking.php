<?php

namespace App\Mail\Contact;

use App\Mail\Mailable;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class Booking extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Variable to store the booking request.
     * @var array
     */
    private $booking;

    /**
     * Create a new message instance.
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->booking = $data;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->replyTo($this->booking['contact_email'], $this->booking['contact_name'])
            ->subject('Booking Request - ' . $this->booking['event_name'] . ' (' . $this->booking['event_dates'] . ')')
            ->markdown('emails.contact.booking')
            ->with('booking', $this->booking);
    }
}
