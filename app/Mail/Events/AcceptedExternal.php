<?php

namespace App\Mail\Events;

use App\Mail\Mailable;
use App\Models\Events\Event;
use Illuminate\Bus\Queueable;
use Illuminate\Http\Request;
use Illuminate\Queue\SerializesModels;

class AcceptedExternal extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * Variable to store the message data.
     * @var array
     */
    private $data;

    /**
     * Create a new message instance.
     *
     * @param Event $event
     * @param Request $request
     */
    public function __construct(Event $event, Request $request)
    {
        $this->data = [
            'event_name'  => $event->name,
            'event_dates' => $event->start_date . ($request->has('one_day') ? '' : (' - ' . $event->end_date)),
            'em'          => $event->em_id ? $event->em->name : '*none yet*',
            'client'      => $event->client,
            'venue'       => $event->venue,
            'venue_type'  => Event::$VenueTypes[$event->venue_type],
            'description' => $event->description,
        ];
    }

    /**
     * Build the message.
     * @return $this
     */
    public function build()
    {
        return $this->replyTo(config('bts.emails.events.accepted_external.reply'))
            ->from(config('bts.emails.events.accepted_external.from'))
            ->cc(config('bts.emails.events.accepted_external.cc'))
                    ->subject('Backstage External Off-Campus Event')
                    ->markdown('emails.events.accepted_external')
                    ->with($this->data);
    }
}
