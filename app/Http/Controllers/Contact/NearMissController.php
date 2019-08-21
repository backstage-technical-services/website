<?php

namespace App\Http\Controllers\Contact;

use App\Http\Controllers\Controller;
use App\Http\Requests\Contact\NearMissRequest;
use App\Mail\Contact\NearMissReport;
use bnjns\LaravelNotifications\Facades\Notify;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Mail;
use Illuminate\View\View;

class NearMissController extends Controller
{
    /**
     * NearMissController constructor.
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Show the form to report a near miss.
     *
     * @return View
     */
    public function showForm()
    {
        $request    = request();
        $user_name  = $request->old('user_name') ? $request->old('user_name') : (auth()->check() ? auth()->user()->name : null);
        $user_email = $request->old('user_email') ? $request->old('user_email') : (auth()->check() ? auth()->user()->email : null);

        return view('contact.near-miss')->with([
            'user_name'  => $user_name,
            'user_email' => $user_email,
        ]);
    }

    /**
     * @param NearMissRequest $request
     *
     * @return RedirectResponse
     */
    public function process(NearMissRequest $request)
    {
        Mail::to(config('bts.emails.safety.near_miss_reports'))
            ->queue(new NearMissReport($request));

        Notify::success('Thank you for reporting the near miss');
        return redirect()->route('home');
    }
}