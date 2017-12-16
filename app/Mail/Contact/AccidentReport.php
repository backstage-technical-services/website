<?php
    
    namespace App\Mail\Contact;
    
    use App\Http\Requests\Contact\AccidentRequest;
    use App\Mail\Mailable;
    use Illuminate\Bus\Queueable;
    use Illuminate\Queue\SerializesModels;
    use Illuminate\Contracts\Queue\ShouldQueue;
    
    class AccidentReport extends Mailable
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
            return $this->replyTo($this->report['contact_email'], $this->report['contact_name'])
                        ->subject('** BTS Accident Report **')
                        ->markdown('emails.contact.accident')
                        ->with('report', $this->report);
        }
    }
