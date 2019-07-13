<?php

namespace App\Mail\Contact;

use App\Http\Requests\Contact\NearMissRequest;
use App\Mail\Mailable;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;

class NearMissReport extends Mailable
{
    use Queueable, SerializesModels;

    /**
     * A variable to store the details of the near miss.
     *
     * @var array
     */
    private $report;

    /**
     * NearMissReport constructor.
     *
     * @param \App\Http\Requests\Contact\NearMissRequest $request
     */
    public function __construct(NearMissRequest $request)
    {
        $this->report = [
            'location'               => $request->location,
            'date'                   => Carbon::createFromFormat('Y-m-d', $request->date),
            'time'                   => Carbon::createFromFormat('H:i', $request->time),
            'details'                => trim(clean($request->details)),
            'safety_recommendations' => trim(clean($request->safety_recommendations)),
            'user_name'              => $request->user_name ?: null,
            'user_email'             => $request->user_email ?: null,
        ];
    }

    public function build()
    {
        return $this->subject('Near miss reported')
                    ->markdown('emails.contact.near-miss')
                    ->with('report', $this->report);
    }
}