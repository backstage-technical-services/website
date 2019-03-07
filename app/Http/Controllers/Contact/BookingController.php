<?php

namespace App\Http\Controllers\Contact;

use App\Http\Controllers\Controller;
use App\Http\Requests\Contact\BookRequest;
use App\Mail\Contact\Booking;
use App\Mail\Contact\BookingReceipt;
use bnjns\LaravelNotifications\Facades\Notify;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;

class BookingController extends Controller
{
    /**
     * Show the booking form.
     *
     * @return \Illuminate\View\View
     */
    public function showForm()
    {
        return view('contact.book');
    }

    /**
     * Process the booking form.
     *
     * @param \App\Http\Requests\Contact\BookRequest $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function process(BookRequest $request)
    {
        $data = $request->all();

        Mail::to('committee@bts-crew.com')
            ->queue(new Booking($data));
        Mail::to($request->get('contact_email'), $request->get('contact_name'))
            ->queue(new BookingReceipt($data));

        Notify::success('Thank you for your booking. You should receive a receipt soon.');
        return redirect()->route('home');
    }

    /**
     * Get the booking terms and conditions.
     *
     * @param   \Illuminate\Http\Request $request
     *
     * @return  \Illuminate\View\View
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
