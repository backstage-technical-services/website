<?php
    
    namespace App\Mail\Contact;
    
    use App\Http\Requests\Contact\EnquiryRequest;
    use App\Mail\Mailable;
    use Illuminate\Bus\Queueable;
    use Illuminate\Queue\SerializesModels;
    use Illuminate\Contracts\Queue\ShouldQueue;
    
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
            return $this->replyTo('committee@bts-crew.com')
                        ->subject('Your enquiry to BTS')
                        ->markdown('emails.contact.enquiry_receipt')
                        ->with('enquiry', $this->enquiry);
        }
    }
