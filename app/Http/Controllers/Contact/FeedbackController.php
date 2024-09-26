<?php

namespace App\Http\Controllers\Contact;

use App\Http\Controllers\Controller;
use App\Http\Requests\Contact\FeedbackRequest;
use App\Mail\Contact\Feedback;
use Illuminate\Support\Facades\Log;
use Package\Notifications\Facades\Notify;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class FeedbackController extends Controller
{
    /**
     * Show the feedback form.
     *
     * @return View
     */
    public function showForm()
    {
        return view('contact.feedback');
    }

    /**
     * Process the feedback form.
     *
     * @param FeedbackRequest $request
     *
     * @return RedirectResponse
     */
    public function process(FeedbackRequest $request)
    {
        Mail::to(config('bts.emails.contact.feedback'))
            ->queue(new Feedback($request->all()));
        Notify::success('Thank you for providing feedback');

        Log::info("Feedback has been sent");
        return redirect()->route('home');
    }
}
