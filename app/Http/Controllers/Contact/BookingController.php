<?php

namespace App\Http\Controllers\Contact;

use App\Http\Controllers\Controller;
use App\Http\Requests\Contact\BookRequest;
use App\Mail\Contact\Booking;
use App\Mail\Contact\BookingReceipt;
use Illuminate\Support\Facades\Log;
use Package\Notifications\Facades\Notify;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class BookingController extends Controller
{
    /**
     * Show the booking form.
     *
     * @return View
     */
    public function showForm()
    {
        return view('contact.book');
    }

    /**
     * Process the booking form.
     *
     * @param BookRequest $request
     *
     * @return RedirectResponse
     */
    public function process(BookRequest $request)
    {
        $data = $request->all();

        Mail::to(config('bts.emails.contact.bookings'))->queue(new Booking($data));
        Mail::to($request->get('contact_email'), $request->get('contact_name'))->queue(new BookingReceipt($data));

        Log::info(
            'Booking request has been sent: ' . json_encode($request->only('event_name', 'event_venue', 'event_dates')),
        );
        Notify::success('Thank you for your booking. You should receive a receipt soon.');
        return redirect()->route('home');
    }

    /**
     * Get the booking terms and conditions.
     *
     * @param Request $request
     *
     * @return  View
     */
    public function getTerms(Request $request)
    {
        if ($request->ajax()) {
            return view('contact.book.modal');
        } else {
            return view('contact.book_terms');
        }
    }
}
