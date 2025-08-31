<?php

namespace App\Http\Controllers\Contact;

use App\Http\Controllers\Controller;
use App\Http\Requests\Contact\AccidentRequest;
use App\Mail\Contact\AccidentReport;
use App\Mail\Contact\AccidentReportReceipt;
use Illuminate\Support\Facades\Log;
use Package\Notifications\Facades\Notify;
use Carbon\Carbon;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

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
     * @return View
     */
    public function showForm()
    {
        return view('contact.accident')->with([
            'PersonTypes' => AccidentRequest::$PersonTypes,
            'Severities' => AccidentRequest::$Severities,
            'PersonTypeDefault' => array_search('Undergraduate', AccidentRequest::$PersonTypes),
        ]);
    }

    /**
     * Process the accident report form.
     *
     * @param AccidentRequest $request
     *
     * @return RedirectResponse
     */
    public function process(AccidentRequest $request)
    {
        // Set the data for the emails
        $request->merge([
            'date_formatted' => Carbon::createFromFormat(
                'Y-m-d H:i',
                $request->get('date') . ' ' . $request->get('time'),
            ),
            'person_type_email' =>
                $request->get('person_type') == 'other'
                    ? $request->get('person_type_other')
                    : AccidentRequest::$PersonTypes[$request->get('person_type')],
            'severity_email' => AccidentRequest::$Severities[$request->get('severity')],
        ]);

        Mail::to(config('bts.emails.safety.accident_reports'))->queue(new AccidentReport($request->all()));
        Mail::to($request->get('contact_email'), $request->get('contact_name'))->queue(
            new AccidentReportReceipt($request->all()),
        );

        Log::info(
            "Accident has been reported for location '{$request->get('location')}' at {$request->get(
                'date_formatted',
            )}",
        );
        Notify::success('Thank you for reporting the accident');
        return redirect()->route('home');
    }
}
