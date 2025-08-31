<?php

namespace App\Mail\Contact;

use App\Mail\Mailable;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;

class EnquiryReceipt extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Variable to store the enquiry.
     * @var array
     */
    private $enquiry;

    /**
     * Create a new message instance.
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->enquiry = $data;
    }

    /**
     * Build the message.
     * @return $this
     */
    public function build()
    {
        return $this->replyTo(config('bts.emails.contact.enquiry_receipt'))
            ->subject('Your enquiry to BTS')
            ->markdown('emails.contact.enquiry_receipt')
            ->with('enquiry', $this->enquiry);
    }
}
