<?php
    
    namespace App\Mail\Contact;
    
    use App\Mail\Mailable;
    use Illuminate\Bus\Queueable;
    use Illuminate\Queue\SerializesModels;

    class BookingReceipt extends Mailable
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
         * @return $this
         */
        public function build()
        {
            list($forename) = explode(' ', $this->booking['contact_name']);

            return $this->replyTo(config('bts.emails.contact.booking_receipt'))
                        ->subject('Booking Request Receipt - ' . $this->booking['event_name'] . ' (' . $this->booking['event_dates'] . ')')
                        ->markdown('emails.contact.booking_receipt')
                        ->with('booking', $this->booking)
                        ->with('forename', $forename);
        }
    }
