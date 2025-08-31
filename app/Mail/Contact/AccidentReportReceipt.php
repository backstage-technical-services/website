<?php

namespace App\Mail\Contact;

use App\Mail\Mailable;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;

class AccidentReportReceipt extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Variable to store the accident report.
     * @var array
     */
    private $report;

    /**
     * Create a new message instance.
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->report = $data;
    }

    /**
     * Build the message.
     * @return $this
     */
    public function build()
    {
        [$forename] = explode(' ', $this->report['contact_name']);

        return $this->replyTo(config('bts.emails.safety.accident_receipt'))
            ->subject('BTS Accident Report Receipt')
            ->markdown('emails.contact.accident_receipt')
            ->with('report', $this->report)
            ->with('forename', $forename);
    }
}
