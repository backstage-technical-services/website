<?php

namespace App\Http\Controllers\Events;

use App\Http\Controllers\Controller;
use App\Http\Requests\Events\EmailRequest;
use App\Models\Events\Event;

class EmailController extends Controller
{
    /**
     * Set the basic authentication requirements.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Send an email to the crew.
     * @param                                        $eventId
     * @param \App\Http\Requests\Events\EmailRequest $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function store($eventId, EmailRequest $request)
    {
        // Authorise
        $this->requireAjax();
        $event = Event::findOrFail($eventId);
        $this->authorize('update', $event);

        // Create the email object
        $email = $event->emails()->create([
            'sender_id' => $request->user()->id,
            'header'    => clean($request->get('header')),
            'body'      => clean($request->get('body')),
        ]);

        // Send the email to the crew
        return $email->send($request->get('crew', 'all'), $request->user());
    }
}