<?php
    
    namespace App\Mail;
    
    use Illuminate\Bus\Queueable;
    use Illuminate\Mail\Mailable as IlluminateMailable;
    use Illuminate\Queue\SerializesModels;
    use Illuminate\Contracts\Queue\ShouldQueue;
    
    class Mailable extends IlluminateMailable
    {
        /**
         * Set the subject of the message.
         * @param  string $subject
         * @return $this
         */
        public function subject($subject)
        {
            return parent::subject('[Backstage Website] ' . $subject);
        }
    }
