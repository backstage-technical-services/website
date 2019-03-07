<?php

namespace App\Http\Controllers\Contact;

use App\Http\Controllers\Controller;
use App\Http\Requests\Contact\AccidentRequest;
use App\Mail\Contact\AccidentReport;
use App\Mail\Contact\AccidentReportReceipt;
use bnjns\LaravelNotifications\Facades\Notify;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

class AccidentController extends Controller
{
    /**
     * Require that the user is authenticated.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the accident report form.
     *
     * @return \Illuminate\View\View
     */
    public function showForm()
    {
        return view('contact.accident')
            ->with([
                'PersonTypes'       => AccidentRequest::$PersonTypes,
                'Severities'        => AccidentRequest::$Severities,
                'PersonTypeDefault' => array_search('Undergraduate', AccidentRequest::$PersonTypes),
            ]);
    }

    /**
     * Process the accident report form.
     *
     * @param \App\Http\Requests\Contact\AccidentRequest $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function process(AccidentRequest $request)
    {
        // Set the data for the emails
        $request->merge([
            'date_formatted'    => Carbon::createFromFormat('Y-m-d H:i', $request->get('date') . ' ' . $request->get('time')),
            'person_type_email' => $request->get('person_type') == 'other' ? $request->get('person_type_other') : AccidentRequest::$PersonTypes[$request->get('person_type')],
            'severity_email'    => AccidentRequest::$Severities[$request->get('severity')],
        ]);

        // TODO: Move these to a config file
        Mail::to([
            'committee@bts-crew.com',
            'safety@bts-crew.com',
            'P.Hawker@bath.ac.uk',
            'P.Brooks@bath.ac.uk ',
        ])
            ->queue(new AccidentReport($request->all()));
        Mail::to($request->get('contact_email'), $request->get('contact_name'))
            ->queue(new AccidentReportReceipt($request->all()));

        Notify::success('Thank you for reporting the accident');
        return redirect()->route('home');
    }
}
