<?php

namespace App\Mail\Equipment;

use Illuminate\Bus\Queueable;
use App\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

class Breakage extends Mailable
{
    use Queueable, SerializesModels;
    
    /**
     * Variable to store the breakage.
     * @var array
     */
    private $breakage;
    
    /**
     * Create a new message instance.
     * @param array $data
     */
    public function __construct(array $data)
    {
        $this->breakage = $data;
    }
    
    /**
     * Build the message.
     * @return $this
     */
    public function build()
    {
        return $this->replyTo($this->breakage['user_email'], $this->breakage['user_name'])
                    ->subject('Equipment breakage')
                    ->markdown('emails.equipment.breakage')
                    ->with('breakage', $this->breakage);
    }
}
