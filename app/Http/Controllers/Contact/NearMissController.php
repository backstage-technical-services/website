<?php

namespace App\Http\Controllers\Contact;

use App\Http\Controllers\Controller;
use App\Http\Requests\Contact\NearMissRequest;
use App\Mail\Contact\NearMissReport;
use bnjns\LaravelNotifications\Facades\Notify;
use Illuminate\Support\Facades\Mail;

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
     * @return \Illuminate\View\View
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
     * @param \App\Http\Requests\Contact\NearMissRequest $request
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function process(NearMissRequest $request)
    {
        Mail::to(['bts@bath.ac.uk', 'safety@bts-crew.com',])
            ->queue(new NearMissReport($request));

        Notify::success('Thank you for reporting the near miss');
        return redirect()->route('home');
    }
}