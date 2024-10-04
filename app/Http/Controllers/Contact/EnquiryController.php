<?php

namespace App\Http\Controllers\Contact;

use App\Http\Controllers\Controller;
use App\Http\Requests\Contact\EnquiryRequest;
use App\Mail\Contact\Enquiry;
use App\Mail\Contact\EnquiryReceipt;
use Illuminate\Support\Facades\Log;
use Package\Notifications\Facades\Notify;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class EnquiryController extends Controller
{
    /**
     * Show the enquiries form.
     *
     * @return View
     */
    public function showForm()
    {
        return view('contact.enquiries');
    }

    /**
     * Process the enquiries form.
     *
     * @param EnquiryRequest $request
     *
     * @return RedirectResponse
     */
    public function process(EnquiryRequest $request)
    {
        $data = $request->all();

        Mail::to(config('bts.emails.contact.enquiries'))
            ->queue(new Enquiry($data));
        Mail::to($request->get('email'), $request->get('name'))
            ->queue(new EnquiryReceipt($data));

        Log::info("General enquiry has been sent");
        Notify::success('Enquiry sent. You should receive a receipt soon.');
        return redirect()->route('home');
    }
}
