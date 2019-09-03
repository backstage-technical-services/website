<?php

namespace App\Mail\Events;

use App\Models\Events\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Http\Request;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class FinanceEmail extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Variable to store the data to pass to the view.
     *
     * @var array
     */
    private $data = [];


    /**
     * Variable to store the name of the view to use.
     *
     * @var string
     */
    private $template;

    /**
     * Create a new message instance.
     *
     * @param Request $request
     * @param Event $event
     */
    public function __construct(Request $request, Event $event)
    {
        $this->template = strtolower($request->get('message'));
        $this->data     = [
            'subject'  => $request->get('subject'),
            'event'    => $event->name,
            'event_id' => $request->get('fsid'),
            'name'     => $event->em->forename,
        ];
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        return $this->replyTo(config('bts.emails.finance'))
                    ->subject($this->data['subject'] . ' (' . $this->data['event'] . ')')
                    ->markdown('emails.events.finance_db.' . $this->template)
                    ->with($this->data);
    }
}
