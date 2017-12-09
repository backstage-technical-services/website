<?php
    
    namespace App\Mail\Contact;
    
    use App\Http\Requests\Contact\EnquiryRequest;
    use App\Mail\Mailable;
    use Illuminate\Bus\Queueable;
    use Illuminate\Queue\SerializesModels;
    use Illuminate\Contracts\Queue\ShouldQueue;
    
    class Enquiry extends Mailable
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
            list($forename) = explode(' ', $this->enquiry['name']);
            
            return $this->replyTo($this->enquiry['email'], $this->enquiry['name'])
                        ->subject('General enquiry')
                        ->markdown('emails.contact.enquiry')
                        ->with('enquiry', $this->enquiry)
                        ->with('forename', $forename);
        }
    }
